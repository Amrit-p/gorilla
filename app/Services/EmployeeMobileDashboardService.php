<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Collection;

class EmployeeMobileDashboardService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * Return today's assigned jobs in route order.
     */
    public function todaysJobs(User $employee): Collection
    {
        return Job::query()
            ->with(['client:id,name,address,phone', 'lead:id,site_instructions'])
            ->whereDate('scheduled_date', now()->toDateString())
            ->whereHas('assignedEmployees', fn ($q) => $q->where('users.id', $employee->id))
            ->orderBy('route_sequence')
            ->orderBy('scheduled_time')
            ->get();
    }

    /**
     * Return completed ledger for current employee.
     */
    public function completedLedger(User $employee, int $limit = 20): Collection
    {
        return Job::query()
            ->with(['client:id,name,address'])
            ->where('status', \App\Enums\JobWorkflowStatus::COMPLETED->value)
            ->whereHas('assignedEmployees', fn ($q) => $q->where('users.id', $employee->id))
            ->latest('scheduled_date')
            ->limit($limit)
            ->get();
    }

    public function updateJobStatus(User $employee, Job $job, string $status): Job
    {
        // Employee can only update jobs assigned to themselves unless manager role.
        $isAssigned = $job->assignedEmployees()->where('users.id', $employee->id)->exists();
        if (! $isAssigned && ! $employee->hasAnyRole(['Office Manager', 'Super Admin'])) {
            abort(403, 'You are not allowed to update this job.');
        }

        $job->status = $status;
        $job->save();

        $job->assignedEmployees()->syncWithoutDetaching([
            $employee->id => [
                'assignment_date' => $job->scheduled_date,
                'assignment_status' => $status,
            ],
        ]);

        $this->activityLogService->log(
            $employee,
            'employee.job_status_updated',
            'Employee updated job status from mobile dashboard.',
            ['job_id' => $job->id, 'status' => $status]
        );

        return $job;
    }
}
