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
                $q->where('client_address', 'like', "%{$search}%")
                    ->orWhereHas('client', function (Builder $clientQuery) use ($search): void {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('customer_unique_id', 'like', "%{$search}%");
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
            CrmConstants::JOB_LIST_SCOPE_HOLD => $query->where('status', JobWorkflowStatus::HOLD->value),
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

        if (! empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        return $query;
    }
}
