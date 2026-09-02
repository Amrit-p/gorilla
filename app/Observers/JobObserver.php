<?php

namespace App\Observers;

use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Services\JobReschedulingService;

class JobObserver
{
    public function __construct(private readonly JobReschedulingService $jobReschedulingService) {}

    /**
     * React to job status transitions:
     *  - Completed  → schedule the next recurring occurrence.
     *  - Hold/Pending → remove the auto-scheduled next occurrence (if still pending).
     */
    public function updated(Job $job): void
    {
        if (! $job->wasChanged('status')) {
            return;
        }

        match ($job->status) {
            JobWorkflowStatus::COMPLETED->value => $this->jobReschedulingService->rescheduleCompletedJob($job),
            JobWorkflowStatus::HOLD->value,
            JobWorkflowStatus::PENDING->value => $this->jobReschedulingService->removeNextScheduledJob($job),
            default => null,
        };
    }
}
