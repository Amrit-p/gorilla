<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Repositories\ActivityLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogModuleService
{
    public function __construct(
        private readonly ActivityLogRepository $activityLogRepository
    ) {}

    public function paginatedLogs(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->activityLogRepository->paginatedList($filters, $perPage);
    }

    /**
     * Build action filter dropdown values.
     *
     * @return array<int, string>
     */
    public function actions(): array
    {
        return ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->values()
            ->all();
    }

    /**
     * Admin-friendly actor list for filtering.
     */
    public function actors()
    {
        return User::query()->orderBy('name')->get(['id', 'name', 'email']);
    }
}
