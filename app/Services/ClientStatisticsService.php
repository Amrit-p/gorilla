<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Job;

class ClientStatisticsService
{
    /**
     * @return array<string, int|float|string|null>
     */
    public function forClient(Client $client): array
    {
        $jobStats = Job::query()
            ->where('client_id', $client->id)
            ->selectRaw("
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_jobs,
                SUM(CASE WHEN status IN ('Pending', 'Assigned', 'En Route', 'On Site') THEN 1 ELSE 0 END) as active_jobs,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_jobs,
                MAX(scheduled_date) as last_job_date
            ")
            ->first();

        return [
            'total_jobs' => (int) ($jobStats->total_jobs ?? 0),
            'completed_jobs' => (int) ($jobStats->completed_jobs ?? 0),
            'active_jobs' => (int) ($jobStats->active_jobs ?? 0),
            'cancelled_jobs' => (int) ($jobStats->cancelled_jobs ?? 0),
            'last_job_date' => $jobStats->last_job_date,
            'lifetime_charges' => (float) ($client->total_charges ?? 0),
        ];
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function forClientJobsTab(Client $client, ?string $statusFilter = null): array
    {
        $query = Job::query()->where('client_id', $client->id);

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $stats = (clone $query)
            ->selectRaw("
                COUNT(*) as filtered_total,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as filtered_completed
            ")
            ->first();

        return [
            'filtered_total' => (int) ($stats->filtered_total ?? 0),
            'filtered_completed' => (int) ($stats->filtered_completed ?? 0),
        ];
    }
}
