<?php

namespace App\Repositories;

use App\Models\Contractor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContractorRepository
{
    /**
     * @param  array{search?: string, status?: string}  $filters
     */
    public function paginatedList(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Contractor::query()->orderBy('name');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findWithContracts(Contractor $contractor): Contractor
    {
        return $contractor->load([
            'contracts' => fn ($q) => $q->orderByDesc('start_date'),
            'contracts.documents',
            'contracts.statusHistories.changedBy',
        ]);
    }
}
