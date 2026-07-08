<?php

namespace App\Services;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Helpers\OptimizationHelper;
use App\Jobs\GeocodeJobAddressJob;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Job;
use App\Models\JobLevel;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Notifications\JobAssignedNotification;
use App\Notifications\JobRescheduledNotification;
use App\Notifications\JobStatusChangedNotification;
use App\Notifications\JobVerifiedNotification;
use App\Repositories\JobRepository;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use App\Support\EquipmentTypes;
use App\Support\ServiceTypes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JobManagementService
{
    public function __construct(
        private readonly JobRepository $jobRepository,
        private readonly JobMowerAssignmentService $mowerAssignmentService,
        private readonly ActivityLogService $activityLogService
    ) {}

    public function paginatedJobs(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->jobRepository->paginatedList($filters, $perPage);
    }

    /**
     * @return Collection<int, Job>
     */
    public function exportJobs(array $filters): Collection
    {
        return $this->jobRepository->exportList($filters);
    }

    public function findForShow(int $jobId): ?Job
    {
        return $this->jobRepository->findForShow($jobId);
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    public function jobTimeline(Job $job, int $limit = 30): Collection
    {
        return $this->jobRepository->timelineForJob($job->id, $limit);
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(?int $selectedClientId = null): array
    {
        $selectedClient = $selectedClientId
            ? Client::query()
                ->with(['equipmentType:id,name,color_code', 'lead.equipmentType:id,name,color_code'])
                ->find($selectedClientId, ['id', 'name', 'address', 'latitude', 'longitude', 'zone_id', 'recurrence_id', 'payment_mode', 'payment_status', 'service_types', 'parking_status', 'customer_type', 'pet_warning', 'additional_site_instructions', 'equipment_type_id', 'lead_id'])
            : null;

        return [
            'clients' => Client::query()
                ->with(['equipmentType:id,name,color_code', 'lead.equipmentType:id,name,color_code'])
                ->orderBy('name')
                ->get(['id', 'name', 'address', 'latitude', 'longitude', 'customer_unique_id', 'zone_id', 'recurrence_id', 'payment_mode', 'payment_status', 'service_types', 'parking_status', 'customer_type', 'pet_warning', 'additional_site_instructions', 'equipment_type_id', 'lead_id']),
            'employees' => User::query()
                ->role(CrmRoles::MOWER)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'efficiency']),
            'serviceTypes' => ServiceTypes::all(),
            'parkingStatuses' => JobParkingStatus::values(),
            'customerTypes' => JobCustomerType::values(),
            'paymentModes' => JobOperationalPaymentMode::values(),
            'paymentStatuses' => JobOperationalPaymentStatus::values(),
            'recurrences' => Recurrence::query()->orderBy('name')->get(['id', 'name']),
            'equipmentTypes' => EquipmentTypes::selectOptions(),
            'jobLevels' => JobLevel::query()->active()->ordered()->get(['id', 'name', 'color_code']),
            'zones' => Zone::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'workflowStatuses' => JobWorkflowStatus::values(),
            'listScopes' => array_merge([
                'today' => 'Today',
                'upcoming' => 'Upcoming',
                'done' => 'Done',
                'completed_unverified' => 'Completed but not verified',
                'hold' => 'Hold',
            ], CrmPermissions::isOfficeManager(auth()->user()) ? [
                'deleted' => 'Deleted jobs',
            ] : []),
            'selectedClient' => $selectedClient,
            'mowerWorkloads' => $this->mowerAssignmentService->mowerWorkloads(now()->toDateString()),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mowerWorkloads(?string $scheduledDate): array
    {
        return $this->mowerAssignmentService->mowerWorkloads($scheduledDate);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function suggestMower(?string $scheduledDate, int $estimatedMinutes): ?array
    {
        return $this->mowerAssignmentService->suggestMower($scheduledDate, $estimatedMinutes);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function createJob(User $actor, array $data, array $images = []): ?Job
    {
        try {
            DB::beginTransaction();
            $employeeIds = $data['employee_ids'] ?? [];
            unset($data['employee_ids']);

            $data['created_by'] = $actor->id;
            $job = Job::query()->create($this->prepareJobData($data, $images));
            GeocodeJobAddressJob::dispatch($job->id);

            if (is_array($employeeIds)) {
                $this->assignEmployeesToJobs($actor, collect([$job]), $job->done_by_user_id, $employeeIds, false);
            }

            $this->activityLogService->log($actor, 'job.created', 'Job created.', ['job_id' => $job->id]);
            DB::commit();

            return $job->fresh(['client', 'assignedEmployees', 'doneByUser']);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return null;
        }
    }

    /**
     * Create a job for a customer, mirroring the field mapping used when a
     * lead is converted. Returns null when the customer has no service types
     * or no schedule date, since jobs require a scheduled date.
     */
    public function createJobFromClient(User $actor, Client $client): ?Job
    {
        if (empty($client->service_types) || $client->schedule_date === null) {
            return null;
        }

        $job = Job::query()->create([
            'client_id' => $client->id,
            'lead_id' => $client->lead_id,
            'zone_id' => $client->zone_id,
            'equipment_type_id' => $client->equipment_type_id,
            'job_level_id' => $client->job_level_id,
            'recurrence_id' => $client->recurrence_id,
            'client_address' => $client->address,
            'latitude' => $client->latitude,
            'longitude' => $client->longitude,
            'required_services' => $client->service_types,
            'scheduled_date' => $client->schedule_date,
            'estimated_duration_minutes' => $client->estimated_time,
            'customer_type' => $client->customer_type,
            'payment_mode' => $client->payment_mode,
            'payment_status' => $client->payment_status,
            'charges' => $client->charges,
            'site_instructions' => $client->additional_site_instructions,
            'special_remarks' => $client->special_remarks,
            'status' => JobWorkflowStatus::PENDING->value,
            'created_by' => $actor->id,
        ]);

        GeocodeJobAddressJob::dispatch($job->id);

        $this->activityLogService->log($actor, 'job.created_from_client', 'Job created from customer.', [
            'job_id' => $job->id,
            'client_id' => $client->id,
        ]);

        return $job;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function updateJob(User $actor, Job $job, array $data, array $images = []): Job
    {
        try {
            DB::beginTransaction();
            $employeeIds = $data['employee_ids'] ?? null;
            unset($data['employee_ids']);

            $job->fill($this->prepareJobData($data, $images, $job));
            $job->save();

            if (is_array($employeeIds)) {
                $this->assignEmployeesToJobs($actor, collect([$job]), $job->done_by_user_id, $employeeIds, false);
            }

            GeocodeJobAddressJob::dispatch($job->id);
            $this->activityLogService->log($actor, 'job.updated', 'Job updated.', ['job_id' => $job->id]);

            DB::commit();

            return $job->fresh(['client', 'assignedEmployees', 'doneByUser']);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return null;
        }
    }

    /**
     * @param  Collection<int, Job>  $jobs
     * @return Collection<int, Job>
     */
    public function assignEmployeesToJobs(
        User $actor,
        Collection $jobs,
        string|int|null $primary_mower_id = null,
        array $helper_mowers = [],
        bool $notify = true,
    ): Collection {
        // Validate mowers before opening a transaction so domain exceptions propagate cleanly.
        $helper_mowers = array_values(array_unique(array_map('intval', $helper_mowers)));
        $this->assertActiveMowerIds($helper_mowers);

        if ($primary_mower_id !== null && $primary_mower_id !== '') {
            $this->assertActiveMowerIds([(int) $primary_mower_id]);
        }

        $incentives = User::query()
            ->whereIn('id', array_filter(array_unique(array_merge($helper_mowers, [(int) $primary_mower_id]))))
            ->pluck('incentive_percentage', 'id');

        try {
            DB::beginTransaction();

            foreach ($jobs as $job) {
                $syncData = [];
                foreach ($helper_mowers as $userId) {
                    $syncData[$userId] = [
                        'assignment_date' => $job->scheduled_date,
                        'assignment_status' => $job->status,
                        'incentive_percentage' => $incentives[$userId] ?? 0,
                    ];
                }

                $job->assignedEmployees()->sync($syncData);
                $job->done_by_user_id = $primary_mower_id;
                $job->incentive_percentage = $incentives[(int) $primary_mower_id] ?? 0;
                $job->save();

                $this->activityLogService->log($actor, 'job.assigned', 'Mowers assigned to job.', [
                    'job_id' => $job->id,
                    'employee_ids' => $helper_mowers,
                ]);
            }

            OptimizationHelper::bumpMapCacheGeneration();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return collect();
        }

        // Reload jobs after commit — one query with eager loads instead of N fresh() calls.
        $freshJobs = Job::with(['client', 'assignedEmployees', 'doneByUser'])
            ->whereIn('id', $jobs->pluck('id'))
            ->get();

        if ($notify && $helper_mowers !== []) {
            User::query()->whereIn('id', $helper_mowers)->get()->each(function (User $employee) use ($freshJobs): void {
                $freshJobs->each(function (Job $job) use ($employee): void {
                    $employee->notify(new JobAssignedNotification($job));
                });
                OptimizationHelper::forgetNotificationUnreadCount($employee->id);
            });
        }

        return $freshJobs;
    }

    /**
     * Verify or unverify a collection of jobs.
     *
     * @param  Collection<int, Job>  $jobs
     */
    public function verifyJobs(User $actor, Collection $jobs, bool $verified = true): Collection
    {
        if ($jobs->isEmpty()) {
            return $jobs;
        }

        DB::transaction(function () use ($actor, $jobs, $verified): void {
            $jobs->each(function (Job $job) use ($actor, $verified): void {
                $job->verified_at = $verified ? now() : null;
                $job->verified_by = $verified ? $actor->id : null;
                $job->save();

                $this->activityLogService->log(
                    $actor,
                    $verified ? 'job.verified' : 'job.verification_removed',
                    $verified ? 'Job verified.' : 'Job verification removed.',
                    ['job_id' => $job->id],
                );
            });
        });

        if ($verified) {
            User::query()
                ->role([CrmRoles::OFFICE_MANAGER])
                ->where('id', '!=', $actor->id)
                ->get()
                ->each(function (User $manager) use ($jobs, $actor): void {
                    $jobs->each(function (Job $job) use ($manager, $actor): void {
                        $manager->notify(new JobVerifiedNotification($job, $actor));
                    });
                    OptimizationHelper::forgetNotificationUnreadCount($manager->id);
                });
        }

        // Reload jobs as an Eloquent collection with `verifier` relation,
        // preserving the original ordering of the provided collection.
        $ids = $jobs->pluck('id')->all();
        $fresh = Job::with('verifier')->whereIn('id', $ids)->get()->keyBy('id');
        $ordered = collect($ids)->map(fn (int $id) => $fresh->get($id));

        return $ordered;
    }

    /**
     * @param  Collection<int, Job>  $jobs
     * @return Collection<int, Job>
     */
    public function updateJobsStatus(User $actor, Collection $jobs, string $status): Collection
    {
        DB::transaction(function () use ($actor, $jobs, $status): void {
            $jobs->each(function (Job $job) use ($actor, $status): void {
                $job->status = $status;
                $job->save();
                $this->activityLogService->log($actor, 'job.status_updated', 'Job status updated to '.$status.'.', [
                    'job_id' => $job->id,
                    'status' => $status,
                ]);
            });
        });

        User::query()->role([CrmRoles::OFFICE_MANAGER])->get()->each(function (User $manager) use ($jobs): void {
            $jobs->each(function (Job $job) use ($manager): void {
                $manager->notify(new JobStatusChangedNotification($job));
            });
            OptimizationHelper::forgetNotificationUnreadCount($manager->id);
        });

        return $jobs;
    }

    /**
     * @param  Collection<int, Job>  $jobs
     * @return Collection<int, Job>
     */
    public function scheduleJobs(User $actor, Collection $jobs, string $scheduledDate, ?string $scheduledTime = null): Collection
    {
        // Normalise to H:i regardless of whether the browser sent H:i:s
        if ($scheduledTime !== null) {
            $scheduledTime = substr($scheduledTime, 0, 5);
        }

        DB::transaction(function () use ($actor, $jobs, $scheduledDate, $scheduledTime): void {
            $jobs->each(function (Job $job) use ($actor, $scheduledDate, $scheduledTime): void {
                $job->scheduled_date = $scheduledDate;
                if ($scheduledTime !== null) {
                    $job->scheduled_time = $scheduledTime;
                }
                $job->status = JobWorkflowStatus::PENDING->value;
                $job->save();
                $this->activityLogService->log($actor, 'job.rescheduled', 'Job rescheduled to '.$scheduledDate.'.', [
                    'job_id' => $job->id,
                    'scheduled_date' => $scheduledDate,
                    'scheduled_time' => $scheduledTime,
                ]);
            });
        });

        User::query()->role([CrmRoles::OFFICE_MANAGER])->get()->each(function (User $manager) use ($jobs): void {
            $jobs->each(function (Job $job) use ($manager): void {
                $manager->notify(new JobRescheduledNotification($job));
            });
            OptimizationHelper::forgetNotificationUnreadCount($manager->id);
        });

        $jobs->each(function (Job $job): void {
            $primaryMower = $job->doneByUser;
            if ($primaryMower) {
                $primaryMower->notify(new JobRescheduledNotification($job));
                OptimizationHelper::forgetNotificationUnreadCount($primaryMower->id);
            }
        });

        return $jobs;
    }

    public function deleteJob(User $actor, Job $job): void
    {
        $job->delete();
        $this->activityLogService->log($actor, 'job.deleted', 'Job deleted.', ['job_id' => $job->id]);
    }

    /**
     * @param  Collection<int, Job>  $jobs
     */
    public function deleteJobs(User $actor, Collection $jobs): int
    {
        DB::transaction(function () use ($actor, $jobs): void {
            $jobs->each(fn (Job $job) => $this->deleteJob($actor, $job));
        });

        return $jobs->count();
    }

    public function restoreJob(User $actor, Job $job): void
    {
        $job->restore();
        $this->activityLogService->log($actor, 'job.restored', 'Job restored.', ['job_id' => $job->id]);
    }

    /**
     * @param  Collection<int, Job>  $jobs
     */
    public function restoreJobs(User $actor, Collection $jobs): int
    {
        DB::transaction(function () use ($actor, $jobs): void {
            $jobs->each(fn (Job $job) => $this->restoreJob($actor, $job));
        });

        return $jobs->count();
    }

    public function forceDeleteJob(User $actor, Job $job): void
    {
        $jobId = $job->id;
        $job->forceDelete();
        $this->activityLogService->log($actor, 'job.force_deleted', 'Job permanently deleted.', ['job_id' => $jobId]);
    }

    /**
     * @param  Collection<int, Job>  $jobs
     */
    public function forceDeleteJobs(User $actor, Collection $jobs): int
    {
        DB::transaction(function () use ($actor, $jobs): void {
            $jobs->each(fn (Job $job) => $this->forceDeleteJob($actor, $job));
        });

        return $jobs->count();
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    public function appendJobImages(User $actor, Job $job, array $images): Job
    {
        if ($images === []) {
            return $job;
        }

        if (! CrmPermissions::canUploadJobImages($actor)) {
            abort(403, 'You are not allowed to upload job images.');
        }

        $job->fill($this->prepareJobData([], $images, $job));
        $job->save();

        $this->activityLogService->log($actor, 'job.images_uploaded', 'Job images uploaded.', [
            'job_id' => $job->id,
            'image_count' => count($images),
        ]);

        return $job;
    }

    /**
     * Prefill site fields from selected customer.
     *
     * @return array<string, mixed>
     */
    public function clientDefaults(int $clientId): array
    {
        $client = Client::query()->with('lead.equipmentType')->findOrFail($clientId);
        $equipmentTypeId = $client->equipment_type_id ?? $client->lead?->equipment_type_id;

        return [
            'client_address' => $client->address,
            'latitude' => $client->latitude,
            'longitude' => $client->longitude,
            'zone_id' => $client->zone_id,
            'recurrence_id' => $client->recurrence_id,
            'customer_type' => $client->customer_type,
            'site_instructions' => $client->additional_site_instructions,
            'equipment_type_id' => $equipmentTypeId,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     * @return array<string, mixed>
     */
    private function prepareJobData(array $data, array $images = [], ?Job $existingJob = null): array
    {
        unset($data['images']);

        $data['status'] ??= JobWorkflowStatus::PENDING->value;
        $data['priority'] ??= 'Medium';
        $data['route_sequence'] ??= 0;
        $data['is_recurring'] ??= false;

        if (! in_array($data['payment_status'] ?? null, [
            JobOperationalPaymentStatus::PENDING->value,
            JobOperationalPaymentStatus::PARTIAL->value,
        ], true)) {
            $data['payment_pending_reason'] = null;
        }

        if (! empty($data['client_id'])) {
            $client = Client::query()->find($data['client_id'], ['lead_id', 'equipment_type_id']);
            if ($client) {
                $data['lead_id'] ??= $client->lead_id;
                if (empty($data['equipment_type_id'])) {
                    $data['equipment_type_id'] = $client->equipment_type_id;
                }
            }
        }

        if (! empty($data['done_by_user_id'])) {
            $this->assertActiveMowerIds([(int) $data['done_by_user_id']]);
        }

        if ($images !== []) {
            $stored = $existingJob?->attached_images ?? [];
            foreach ($images as $image) {
                $stored[] = Storage::disk('public')->putFile('job-images', $image);
            }
            $data['attached_images'] = $stored;
        }

        return $data;
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private function assertActiveMowerIds(array $userIds): void
    {
        if ($userIds === []) {
            return;
        }

        $mowerCount = User::query()
            ->role(CrmRoles::MOWER)
            ->where('is_active', true)
            ->whereIn('id', $userIds)
            ->count();

        if ($mowerCount !== count($userIds)) {
            abort(422, 'Jobs can only be assigned to active mowers.');
        }
    }
}
