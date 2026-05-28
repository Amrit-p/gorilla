<?php

namespace App\Repositories;

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
        $query = Job::query()
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
                'parking_status',
                'customer_type',
                'payment_mode',
                'payment_status',
                'done_by_user_id',
            ])
            ->with([
                'client:id,name,address,customer_unique_id',
                'zone:id,name',
                'assignedEmployees:id,name,efficiency',
                'doneByUser:id,name',
            ])
            ->orderByDesc('scheduled_date')
            ->orderBy('scheduled_time');

        $this->jobListFilter->apply($query, $filters);

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
