<?php

namespace App\Services;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Helpers\OptimizationHelper;
use App\Jobs\GeocodeJobAddressJob;
use App\Models\Client;
use App\Models\Job;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Notifications\JobAssignedNotification;
use App\Notifications\JobStatusChangedNotification;
use App\Repositories\JobRepository;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use App\Support\EquipmentTypes;
use App\Support\ServiceTypes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
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
     * @return Collection<int, \App\Models\ActivityLog>
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
            'parkingStatuses' => \App\Enums\JobParkingStatus::values(),
            'customerTypes' => \App\Enums\JobCustomerType::values(),
            'paymentModes' => \App\Enums\JobOperationalPaymentMode::values(),
            'paymentStatuses' => JobOperationalPaymentStatus::values(),
            'recurrences' => Recurrence::query()->orderBy('name')->get(['id', 'name']),
            'equipmentTypes' => EquipmentTypes::selectOptions(),
            'zones' => Zone::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'workflowStatuses' => JobWorkflowStatus::values(),
            'listScopes' => [
                'today' => 'Today',
                'upcoming' => 'Upcoming',
                'done' => 'Done',
                'hold' => 'Hold',
            ],
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
    public function createJob(User $actor, array $data, array $images = []): Job
    {
        $employeeIds = $data['employee_ids'] ?? [];
        unset($data['employee_ids']);

        $data['created_by'] = $actor->id;
        $job = Job::query()->create($this->prepareJobData($data, $images));
        GeocodeJobAddressJob::dispatch($job->id);

        if ($employeeIds !== []) {
            $this->assignEmployees($actor, $job, $employeeIds, false);
        }

        $this->activityLogService->log($actor, 'job.created', 'Job created.', ['job_id' => $job->id]);

        return $job->fresh(['client', 'assignedEmployees', 'doneByUser']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function updateJob(User $actor, Job $job, array $data, array $images = []): Job
    {
        $employeeIds = $data['employee_ids'] ?? null;
        unset($data['employee_ids']);

        $job->fill($this->prepareJobData($data, $images, $job));
        $job->save();

        if (is_array($employeeIds)) {
            $this->assignEmployees($actor, $job, $employeeIds, false);
        }

        GeocodeJobAddressJob::dispatch($job->id);
        $this->activityLogService->log($actor, 'job.updated', 'Job updated.', ['job_id' => $job->id]);

        return $job->fresh(['client', 'assignedEmployees', 'doneByUser']);
    }

    public function assignEmployees(User $actor, Job $job, array $userIds, bool $notify = true): void
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $this->assertActiveMowerIds($userIds);

        $syncData = [];
        foreach ($userIds as $userId) {
            $syncData[$userId] = [
                'assignment_date' => $job->scheduled_date,
                'assignment_status' => JobWorkflowStatus::STARTED->value,
            ];
        }
        $job->assignedEmployees()->sync($syncData);

        if (! $job->done_by_user_id && count($userIds) === 1) {
            $job->done_by_user_id = (int) $userIds[0];
            $job->save();
        }

        if ($job->status !== JobWorkflowStatus::COMPLETED->value && $job->status !== JobWorkflowStatus::HOLD->value) {
            $job->status = JobWorkflowStatus::STARTED->value;
            $job->save();
        }

        if ($notify) {
            User::query()->whereIn('id', $userIds)->get()->each(function (User $employee) use ($job): void {
                $employee->notify(new JobAssignedNotification($job));
                OptimizationHelper::forgetNotificationUnreadCount($employee->id);
            });
        }
        $job->incentive_percentage = User::find((int)$userIds[0])->incentive_percentage ?? 0;
        $this->activityLogService->log($actor, 'job.assigned', 'Mowers assigned to job.', [
            'job_id' => $job->id,
            'employee_ids' => $userIds,
        ]);
    }

    public function updateStatus(User $actor, Job $job, string $status): Job
    {
        $job->status = $status;
        $job->save();

        User::query()->role([CrmRoles::OFFICE_MANAGER])->get()->each(function (User $manager) use ($job): void {
            $manager->notify(new JobStatusChangedNotification($job));
            OptimizationHelper::forgetNotificationUnreadCount($manager->id);
        });

        $this->activityLogService->log($actor, 'job.status_updated', 'Job status updated to '.$status.'.', [
            'job_id' => $job->id,
            'status' => $status,
        ]);

        return $job;
    }

    public function deleteJob(User $actor, Job $job): void
    {
        $job->delete();
        $this->activityLogService->log($actor, 'job.deleted', 'Job deleted.', ['job_id' => $job->id]);
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

        $data['status'] ??= JobWorkflowStatus::STARTED->value;
        $data['priority'] ??= 'Medium';
        $data['route_sequence'] ??= 0;
        $data['is_recurring'] ??= false;

        if (($data['payment_status'] ?? null) !== JobOperationalPaymentStatus::PENDING->value) {
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
