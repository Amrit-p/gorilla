<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Job;
use App\Support\QueryFilters\ClientListFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ClientRepository
{
    public function __construct(
        private readonly ClientListFilter $clientListFilter
    ) {}

    public function paginatedList(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Client::query()
            ->select([
                'id',
                'customer_unique_id',
                'lead_id',
                'zone_id',
                'name',
                'email',
                'phone',
                'address',
                'service_types',
                'job_type',
                'client_type',
                'customer_type',
                'parking_status',
                'charges',
                'payment_mode',
                'payment_status',
                'created_at',
            ])
            ->with([
                'creator:id,name',
                'lead:id,client_name,status',
                'zone:id,name',
            ])
            ->withCount('jobs')
            ->latest();

        $this->clientListFilter->apply($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    public function findForShow(int $clientId): ?Client
    {
        return Client::query()
            ->with([
                'creator:id,name',
                'lead:id,client_name,status,converted_at',
            ])
            ->withCount([
                'jobs',
                'jobs as completed_jobs_count' => fn (Builder $q) => $q->where('status', 'Completed'),
            ])
            ->find($clientId);
    }
    /**
     * @deprecated This method is no longer used and will be removed in a future release. use JobRepository::paginatedList instead.
     */
    public function paginatedJobsForClient(int $clientId, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Job::query()
            ->select([
                'id',
                'client_id',
                'scheduled_date',
                'scheduled_time',
                'status',
                'required_services',
                'parking_status',
                'customer_type',
                'done_by_user_id',
                'payment_mode',
                'payment_status',
                'client_address',
                'estimated_duration_minutes',
            ])
            ->with(['doneByUser:id,name', 'assignedEmployees:id,name', 'client:id,name,customer_unique_id'])
            ->where('client_id', $clientId)
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time');

        if (! empty($filters['job_status'])) {
            $query->where('status', $filters['job_status']);
        }

        if (! empty($filters['job_search'])) {
            $search = trim((string) $filters['job_search']);
            $query->where(function (Builder $q) use ($search): void {
                $q->where('client_address', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

}
