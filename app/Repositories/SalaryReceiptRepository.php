<?php

namespace App\Repositories;

use App\Models\SalaryReceipt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SalaryReceiptRepository
{
    public function paginatedList(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function exportList(array $filters): Collection
    {
        return $this->baseQuery($filters)->get();
    }

    private function baseQuery(array $filters): Builder
    {
        $query = SalaryReceipt::query()
            ->with([
                'mower:id,name,user_unique_id',
                'generatedBy:id,name',
            ])
            ->latest('period_start');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('mower', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if (! empty($filters['mower_id'])) {
            $query->where('mower_id', $filters['mower_id']);
        }

        if (! empty($filters['period_type'])) {
            $query->where('period_type', $filters['period_type']);
        }

        if (! empty($filters['date_range_start'])) {
            $query->whereDate('period_start', '>=', $filters['date_range_start']);
        }

        if (! empty($filters['date_range_end'])) {
            $query->whereDate('period_end', '<=', $filters['date_range_end']);
        }

        return $query;
    }
}
