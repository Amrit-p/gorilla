<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Job;
use App\Support\QueryFilters\ClientListFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
                'accounting_level_id',
                'client_rating_id',
                'job_level_id',
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
                'recurrence_id',
                'created_at',
            ])
            ->with([
                'creator:id,name',
                'lead:id,client_name,status',
                'zone:id,name',
                'accountingLevel:id,name,description',
                'clientRating:id,name,description',
                'jobLevel:id,name,description',
                'equipmentType:id,name',
                'recurrence:id,name',
                'previousJobs' => fn ($q) => $q->select(['id', 'client_id', 'scheduled_date']),
                'nextJob',
            ]);

        $sort = $filters['sort'] ?? '';
        $direction = in_array($filters['direction'] ?? '', ['asc', 'desc']) ? $filters['direction'] : 'asc';
        $today = now()->toDateString();

        if ($sort === 'last_job') {
            $query
                ->orderByRaw(
                    '(SELECT MAX(scheduled_date) FROM service_jobs WHERE client_id = clients.id AND scheduled_date < ? AND deleted_at IS NULL) IS NULL',
                    [$today]
                )
                ->orderByRaw(
                    "(SELECT MAX(scheduled_date) FROM service_jobs WHERE client_id = clients.id AND scheduled_date < ? AND deleted_at IS NULL) {$direction}",
                    [$today]
                );
        } elseif ($sort === 'next_job') {
            $query
                ->orderByRaw(
                    '(SELECT MIN(scheduled_date) FROM service_jobs WHERE client_id = clients.id AND scheduled_date >= ? AND deleted_at IS NULL) IS NULL',
                    [$today]
                )
                ->orderByRaw(
                    "(SELECT MIN(scheduled_date) FROM service_jobs WHERE client_id = clients.id AND scheduled_date >= ? AND deleted_at IS NULL) {$direction}",
                    [$today]
                );
        } else {
            $query->latest();
        }

        $this->clientListFilter->apply($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    public function exportList(array $filters): Collection
    {
        $query = Client::query()
            ->with([
                'zone:id,name',
                'accountingLevel:id,name,description',
                'jobLevel:id,name,description',
                'equipmentType:id,name',
                'recurrence:id,name',
                'lead:id,client_name,status',
                'creator:id,name',
            ])
            ->latest();

        $this->clientListFilter->apply($query, $filters);

        return $query->get();
    }

    public function findForShow(int $clientId): ?Client
    {
        return Client::query()
            ->with([
                'creator:id,name',
                'lead:id,client_name,status,converted_at',
                'documents' => fn ($q) => $q->latest(),
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
