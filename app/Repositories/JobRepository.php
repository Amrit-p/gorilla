<?php

namespace App\Repositories;

use App\Enums\JobWorkflowStatus;
use App\Models\ActivityLog;
use App\Models\Job;
use App\Support\QueryFilters\JobListFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
                'lead_id',
                'zone_id',
                'customer_name',
                'phone',
                'email',
                'client_address',
                'scheduled_date',
                'scheduled_time',
                'estimated_duration_minutes',
                'required_services',
                'status',
                'priority',
                'numeric_priority',
                'customer_type',
                'payment_mode',
                'payment_status',
                'done_by_user_id',
                'recurrence_id',
                'charges',
                'incentive_percentage',
                'equipment_type_id',
                'job_level_id',
                'client_rating_id',
                'accounting_level_id',
                'special_remarks',
                'internal_notes',
                'contract_id',
                'verified_at',
                'verified_by',
            ])
            ->with([
                'clientRating:id,name,description',
                'accountingLevel:id,name',
                'zone:id,name',
                'recurrence:id,name',
                'equipmentType:id,name,color_code',
                'jobLevel:id,name,color_code',
                'assignedEmployees:id,name,efficiency',
                'doneByUser:id,name',
                'verifier:id,name',
                'contract:id,contractor_id,name,start_date,end_date,status',
                'contract.contractor:id,name,phone,email',
            ]);

        return $query->paginate($perPage)->withQueryString();
    }

    public function findForShow(int $jobId): ?Job
    {
        return Job::query()
            ->with([
                'zone:id,name',
                'accountingLevel:id,name',
                'clientRating:id,name',
                'recurrence:id,name',
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
                'zone:id,name',
                'recurrence:id,name',
                'equipmentType:id,name,color_code',
                'accountingLevel:id,name',
                'clientRating:id,name',
                'assignedEmployees:id,name',
                'doneByUser:id,name',
                'creator:id,name',
            ])
            ->get();
    }

    /**
     * @return Builder<Job>
     */
    private function baseListQuery(array $filters): Builder
    {
        $query = Job::query()
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('scheduled_time', 'desc')
            ->orderByRaw('CASE WHEN numeric_priority IS NULL THEN 1 ELSE 0 END ASC, numeric_priority ASC')
            ->orderByRaw('CASE status WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 WHEN ? THEN 4 ELSE 5 END ASC', [
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
