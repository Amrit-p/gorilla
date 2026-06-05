<?php

namespace App\Repositories;

use App\Enums\JobWorkflowStatus;
use App\Models\ActivityLog;
use App\Models\Job;
use App\Support\QueryFilters\JobListFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class JobRepository
{
    public function __construct(
        private readonly JobListFilter $jobListFilter
    ) {}

    public function paginatedList(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->baseListQuery($filters)
            ->select([
                'id',
                'client_id',
                'zone_id',
                'client_address',
                'scheduled_date',
                'scheduled_time',
                'estimated_duration_minutes',
                'required_services',
                'status',
                'priority',
                'numeric_priority',
                'parking_status',
                'customer_type',
                'payment_mode',
                'payment_status',
                'done_by_user_id',
                'recurrence_id',
                'charges',
                'incentive_percentage',
                'equipment_type_id',
                'job_level_id',
            ])
            ->with([
                'client:id,name,address,customer_unique_id,phone,email,customer_type',
                'zone:id,name',
                'recurrence:id,name',
                'equipmentType:id,name,color_code',
                'jobLevel:id,name,color_code',
                'assignedEmployees:id,name,efficiency',
                'doneByUser:id,name',
            ]);

        return $query->paginate($perPage)->withQueryString();
    }

    public function findForShow(int $jobId): ?Job
    {
        return Job::query()
            ->with([
                'client:id,customer_unique_id,name,address,phone,email,customer_type,parking_status,pet_warning',
                'assignedEmployees:id,name,efficiency',
                'doneByUser:id,name,efficiency',
                'creator:id,name',
            ])
            ->find($jobId);
    }

    /**
     * @return Collection<int, Job>
     */
    public function exportList(array $filters): Collection
    {
        return $this->baseListQuery($filters)
            ->with([
                'client:id,name,customer_unique_id,address,phone,email,customer_type',
                'zone:id,name',
                'recurrence:id,name',
                'equipmentType:id,name,color_code',
                'assignedEmployees:id,name',
                'doneByUser:id,name',
                'creator:id,name',
            ])
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Job>
     */
    private function baseListQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Job::query()
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('scheduled_time', 'desc')
            ->orderByRaw('CASE WHEN numeric_priority IS NULL THEN 1 ELSE 0 END ASC, numeric_priority ASC')
            ->orderByRaw("CASE status WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 WHEN ? THEN 4 ELSE 5 END ASC", [
                JobWorkflowStatus::PENDING->value,
                JobWorkflowStatus::STARTED->value,
                JobWorkflowStatus::HOLD->value,
                JobWorkflowStatus::COMPLETED->value,
            ]);

        $this->jobListFilter->apply($query, $filters);

        return $query;
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    public function timelineForJob(int $jobId, int $limit = 30): Collection
    {
        return ActivityLog::query()
            ->with('user:id,name')
            ->whereJsonContains('context->job_id', $jobId)
            ->latest()
            ->limit($limit)
            ->get();
    }

}
