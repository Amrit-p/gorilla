<?php

namespace App\Services;

use App\Enums\JobImageKind;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\JobLevel;
use App\Models\User;
use App\Support\CrmRoles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class MowerDashboardService
{
    public function __construct(
        private readonly JobImageManagementService $jobImageManagementService,
        private readonly ActivityLogService $activityLogService
    ) {}

    public function assertAssigned(User $mower, Job $job): void
    {
        if ($mower->hasRole(CrmRoles::OFFICE_MANAGER)) {
            return;
        }

        $isAssigned = $job->assignedEmployees()->where('users.id', $mower->id)->exists()
            || (int) $job->done_by_user_id === $mower->id;

        if (! $isAssigned) {
            abort(403, 'This job is not assigned to you.');
        }
    }

    /**
     * @return Collection<int, Job>
     */
    public function assignedJobs(User $mower, ?string $scope = 'today', ?string $scheduleDate = null): Collection
    {
        $query = Job::query()
            ->select([
                'id',
                'client_id',
                'client_address',
                'latitude',
                'longitude',
                'scheduled_date',
                'scheduled_time',
                'estimated_duration_minutes',
                'consumed_time_minutes',
                'status',
                'payment_status',
                'route_sequence',
            ])
            ->with([
                'client:id,customer_unique_id,name,phone,email,address',
            ])
            ->where(function ($q) use ($mower) {
                $q->whereHas('assignedEmployees', fn ($q) => $q->where('users.id', $mower->id))
                  ->orWhere('done_by_user_id', $mower->id);
            });

        $today = now()->toDateString();
        $targetDate = $scheduleDate ?? $today;

        match ($scope) {
            'upcoming' => $query->whereDate('scheduled_date', '>', $today)
                ->where('status', '!=', JobWorkflowStatus::COMPLETED->value),
            'completed' => $query->where('status', JobWorkflowStatus::COMPLETED->value),
            'hold' => $query->where('status', JobWorkflowStatus::HOLD->value),
            'pending' => $query->whereIn('status', [JobWorkflowStatus::STARTED->value, JobWorkflowStatus::HOLD->value, JobWorkflowStatus::PENDING->value])
                ->whereDate('scheduled_date', '<=', $today),
            'started' => $query->where('status', JobWorkflowStatus::STARTED->value),
            'today-special' => $query->whereDate('scheduled_date', $targetDate)
                ->whereHas('jobLevel', fn ($q) => $q->where('name', 'Special')),
            'today' => $query->whereDate('scheduled_date', $targetDate),
            default => $query->whereDate('scheduled_date', '<=', $today)
                ->whereNotIn('status', [JobWorkflowStatus::COMPLETED->value]),
        };
        return $query
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('scheduled_time', 'desc')
            ->orderByRaw('CASE WHEN numeric_priority IS NULL THEN 1 ELSE 0 END ASC, numeric_priority ASC')
            ->orderByRaw("CASE status WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 WHEN ? THEN 4 ELSE 5 END ASC", [
                JobWorkflowStatus::PENDING->value,
                JobWorkflowStatus::STARTED->value,
                JobWorkflowStatus::HOLD->value,
                JobWorkflowStatus::COMPLETED->value,
            ])
            ->get();
    }

    public function jobDetail(User $mower, Job $job): Job
    {
        $this->assertAssigned($mower, $job);

        return $job->load([
            'client:id,customer_unique_id,name,phone,email,address,service_types,parking_status,customer_type,pet_warning,additional_site_instructions,payment_mode,payment_status',
            'assignedEmployees:id,name,efficiency',
        ]);
    }

    public function updateStatus(User $mower, Job $job, string $status): Job
    {
        $this->assertAssigned($mower, $job);

        if (! in_array($status, JobWorkflowStatus::values(), true)) {
            abort(422, 'Invalid job status.');
        }

        $job->status = $status;
        $job->save();

        $job->assignedEmployees()->syncWithoutDetaching([
            $mower->id => [
                'assignment_date' => $job->scheduled_date,
                'assignment_status' => $status,
            ],
        ]);

        $this->activityLogService->log($mower, 'mower.job_status_updated', 'Mower updated job status.', [
            'job_id' => $job->id,
            'status' => $status,
        ]);

        return $job;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePayment(User $mower, Job $job, array $data): Job
    {
        $this->assertAssigned($mower, $job);

        $job->payment_status = $data['payment_status'];
        $isPartial = $data['payment_status'] === JobOperationalPaymentStatus::PARTIAL->value;
        $needsReason = $isPartial || $data['payment_status'] === JobOperationalPaymentStatus::PENDING->value;
        $job->payment_pending_reason = $needsReason ? ($data['payment_pending_reason'] ?? null) : null;
        $job->save();

        $this->activityLogService->log($mower, 'mower.payment_updated', 'Mower updated job payment status.', [
            'job_id' => $job->id,
            'payment_status' => $job->payment_status,
        ]);

        return $job;
    }

    public function updateConsumedTime(User $mower, Job $job, int $minutes): Job
    {
        $this->assertAssigned($mower, $job);

        $job->consumed_time_minutes = $minutes;
        $job->save();

        $this->activityLogService->log($mower, 'mower.consumed_time_updated', 'Mower logged consumed time.', [
            'job_id' => $job->id,
            'consumed_time_minutes' => $minutes,
        ]);

        return $job;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, array<string, mixed>>
     */
    public function uploadBeforeImages(User $mower, Job $job, array $files): array
    {
        $this->assertAssigned($mower, $job);

        return $this->jobImageManagementService->upload($job, JobImageKind::BEFORE, $files, $mower);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, array<string, mixed>>
     */
    public function uploadAfterImages(User $mower, Job $job, array $files): array
    {
        $this->assertAssigned($mower, $job);

        return $this->jobImageManagementService->upload($job, JobImageKind::AFTER, $files, $mower);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function deleteBeforeImage(User $mower, Job $job, string $imageId): array
    {
        $this->assertAssigned($mower, $job);

        return $this->jobImageManagementService->delete($job, JobImageKind::BEFORE, $imageId, $mower);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function deleteAfterImage(User $mower, Job $job, string $imageId): array
    {
        $this->assertAssigned($mower, $job);

        return $this->jobImageManagementService->delete($job, JobImageKind::AFTER, $imageId, $mower);
    }
}
