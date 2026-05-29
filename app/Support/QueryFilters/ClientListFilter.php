<?php

namespace App\Support\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

final class ClientListFilter
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function apply(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('customer_unique_id', 'like', "%{$search}%");
            });
        }

        foreach (['zone_id', 'job_type', 'customer_type', 'parking_status', 'payment_status', 'client_type'] as $column) {
            if (! empty($filters[$column])) {
                $query->where($column, $filters[$column]);
            }
        }

        if (($filters['from_lead'] ?? '') === '1') {
            $query->whereNotNull('lead_id');
        } elseif (($filters['from_lead'] ?? '') === '0') {
            $query->whereNull('lead_id');
        }

        if(! empty($filters['recurrence_id'])) {
            $query->where('recurrence_id', $filters['recurrence_id']);
        }

        return $query;
    }
}
