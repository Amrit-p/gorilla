<?php

namespace App\Repositories;

use App\Enums\FollowupStatus;
use App\Models\Followup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FollowupRepository
{
    /**
     * @param  array{search?: string, status?: string, followable_type?: string, assigned_to?: string, next_followup_from?: string, next_followup_to?: string}  $filters
     */
    public function paginatedList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Followup::query()
            ->with(['followable', 'createdBy', 'assignedTo'])
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['followable_type'])) {
            $filters['followable_type'] === Followup::GENERAL_TYPE
                ? $query->whereNull('followable_type')
                : $query->where('followable_type', $filters['followable_type']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhere('outcome', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        // Undated follow-ups are scheduled against their creation date, matching the 3-week grid.
        if (! empty($filters['next_followup_from'])) {
            $query->whereRaw(
                'DATE(COALESCE(next_followup_at, created_at)) >= ?',
                [$filters['next_followup_from']]
            );
        }

        if (! empty($filters['next_followup_to'])) {
            $query->whereRaw(
                'DATE(COALESCE(next_followup_at, created_at)) <= ?',
                [$filters['next_followup_to']]
            );
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return Collection<int, Followup>
     */
    public function dueTodayPending(): Collection
    {
        return Followup::query()
            ->with(['createdBy', 'assignedTo'])
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
            ->with(['createdBy', 'assignedTo'])
            ->where('followable_type', $type)
            ->where('followable_id', $id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findWithRelations(Followup $followup): Followup
    {
        return $followup->load(['followable', 'createdBy', 'assignedTo']);
    }
}
