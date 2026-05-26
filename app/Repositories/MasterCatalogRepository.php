<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class MasterCatalogRepository
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function paginatedList(string $modelClass, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $modelClass::query()->ordered();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        if (($filters['status'] ?? '') !== '') {
            $query->where('is_active', (bool) $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
