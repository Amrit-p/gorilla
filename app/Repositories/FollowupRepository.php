<?php

namespace App\Repositories;

use App\Enums\FollowupStatus;
use App\Models\Followup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FollowupRepository
{
    /**
     * @param  array{search?: string, status?: string, followable_type?: string, next_followup_from?: string, next_followup_to?: string}  $filters
     */
    public function paginatedList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Followup::query()
            ->with(['followable', 'createdBy'])
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['followable_type'])) {
            $query->where('followable_type', $filters['followable_type']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('outcome', 'like', "%{$search}%");
        }

        if (! empty($filters['next_followup_from'])) {
            $query->whereDate('next_followup_at', '>=', $filters['next_followup_from']);
        }

        if (! empty($filters['next_followup_to'])) {
            $query->whereDate('next_followup_at', '<=', $filters['next_followup_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return Collection<int, Followup>
     */
    public function dueTodayPending(): Collection
    {
        return Followup::query()
            ->with(['createdBy'])
            ->whereDate('next_followup_at', today())
            ->where('status', FollowupStatus::Pending->value)
            ->get();
    }

    /**
     * @return Collection<int, Followup>
     */
    public function forFollowable(string $type, int $id): Collection
    {
        return Followup::query()
            ->with(['createdBy'])
            ->where('followable_type', $type)
            ->where('followable_id', $id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findWithRelations(Followup $followup): Followup
    {
        return $followup->load(['followable', 'createdBy']);
    }
}
