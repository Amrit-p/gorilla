<?php

namespace App\Repositories;

use App\Enums\UserEfficiency;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function paginatedList(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()
            ->with('roles')
            ->orderBy('user_unique_id');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $q->orWhere('user_unique_id', (int) $search);
                }
            });
        }

        if (! empty($filters['status']) && in_array($filters['status'], UserStatus::values(), true)) {
            $query->where('status', $filters['status']);
        } elseif (($filters['status'] ?? '') !== '' && in_array((string) $filters['status'], ['0', '1'], true)) {
            $query->where('is_active', (bool) $filters['status']);
        }

        if (! empty($filters['efficiency']) && in_array($filters['efficiency'], UserEfficiency::values(), true)) {
            $query->where('efficiency', $filters['efficiency']);
        }

        if (! empty($filters['role'])) {
            $query->role($filters['role']);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
