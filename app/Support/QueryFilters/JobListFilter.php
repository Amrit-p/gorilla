<?php

namespace App\Support\QueryFilters;

use App\Enums\JobWorkflowStatus;
use App\Support\CrmConstants;
use Illuminate\Database\Eloquent\Builder;

final class JobListFilter
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function apply(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search): void {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('client_address', 'like', "%{$search}%")
                    ->orWhereHas('client', function (Builder $clientQuery) use ($search): void {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('customer_unique_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignedEmployees', function (Builder $empQuery) use ($search): void {
                        $empQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('doneByUser', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $today = now()->toDateString();

        match ((string) ($filters['list_scope'] ?? '')) {
            CrmConstants::JOB_LIST_SCOPE_TODAY => $query->whereDate('scheduled_date', $today),
            CrmConstants::JOB_LIST_SCOPE_UPCOMING => $query
                ->whereDate('scheduled_date', '>', $today)
                ->where('status', '!=', JobWorkflowStatus::COMPLETED->value),
            CrmConstants::JOB_LIST_SCOPE_DONE => $query->where('status', JobWorkflowStatus::COMPLETED->value),
            CrmConstants::JOB_LIST_SCOPE_COMPLETED_UNVERIFIED => $query
                ->where('status', JobWorkflowStatus::COMPLETED->value)
                ->whereNull('verified_at'),
            CrmConstants::JOB_LIST_SCOPE_HOLD => $query->where('status', JobWorkflowStatus::HOLD->value),
            CrmConstants::JOB_LIST_SCOPE_DELETED => $query->onlyTrashed(),
            default => null,
        };

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['zone_id'])) {
            $query->where('zone_id', $filters['zone_id']);
        }

        if (! empty($filters['done_by_user_id'])) {
            $uid = (int) $filters['done_by_user_id'];
            $query->where(function (Builder $q) use ($uid): void {
                $q->where('done_by_user_id', $uid)
                    ->orWhereHas('assignedEmployees', fn (Builder $e) => $e->where('users.id', $uid));
            });
        }

        if (! empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (! empty($filters['recurrence_id'])) {
            $query->where('recurrence_id', $filters['recurrence_id']);
        }

        if (! empty($filters['assignment'])) {
            if ($filters['assignment'] === 'assigned') {
                $query->whereHas('assignedEmployees');
            } elseif ($filters['assignment'] === 'unassigned') {
                $query->whereDoesntHave('assignedEmployees');
            }
        }

        if (! empty($filters['payment_mode'])) {
            $query->where('payment_mode', $filters['payment_mode']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['equipment_type_id'])) {
            $query->where('equipment_type_id', $filters['equipment_type_id']);
        }

        if (! empty($filters['job_level_id'])) {
            $query->where('job_level_id', $filters['job_level_id']);
        }

        if (! empty($filters['customer_type'])) {
            $query->where('customer_type', $filters['customer_type']);
        }

        if (! empty($filters['service_type'])) {
            $query->whereJsonContains('required_services', $filters['service_type']);
        }

        if (! empty($filters['date_range_start'])) {
            $query->whereDate('scheduled_date', '>=', $filters['date_range_start']);
        }

        if (! empty($filters['date_range_end'])) {
            $query->whereDate('scheduled_date', '<=', $filters['date_range_end']);
        }

        if (! empty($filters['contractor_id'])) {
            $query->whereHas('contract', fn (Builder $q) => $q->where('contractor_id', (int) $filters['contractor_id']));
        }

        return $query;
    }
}
