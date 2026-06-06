<?php

namespace App\Repositories;

use App\Models\Checklist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChecklistRepository
{
    public function paginatedList(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Checklist::query()->withCount('points')->latest();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
