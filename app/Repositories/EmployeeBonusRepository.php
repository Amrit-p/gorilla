<?php

namespace App\Repositories;

use App\Models\EmployeeBonus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EmployeeBonusRepository
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
        $query = EmployeeBonus::query()
            ->select(['id', 'user_id', 'amount', 'description', 'bonus_date', 'created_by', 'created_at'])
            ->with([
                'employee:id,name,user_unique_id',
                'creator:id,name',
            ])
            ->latest('bonus_date');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('employee', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['date_range_start'])) {
            $query->whereDate('bonus_date', '>=', $filters['date_range_start']);
        }

        if (! empty($filters['date_range_end'])) {
            $query->whereDate('bonus_date', '<=', $filters['date_range_end']);
        }

        return $query;
    }
}
