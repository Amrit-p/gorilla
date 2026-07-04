<?php

namespace App\Services;

use App\Enums\JobOperationalPaymentStatus;
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
     * @return array{total_jobs: int, completed_jobs: int, pending_amount: float, last_job_date: string|null, last_mowers: array<int, string>, pending_jobs: array<int, array{id: int, scheduled_date: string|null, payment_status: string|null, charges: float}>}
     */
    public function forJobShowSidebar(Client $client, int $excludeJobId): array
    {
        $jobStats = Job::query()
            ->where('client_id', $client->id)
            ->selectRaw('
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_jobs,
                SUM(CASE WHEN payment_status != ? THEN charges ELSE 0 END) as pending_amount
            ', ['Completed', JobOperationalPaymentStatus::RECEIVED->value])
            ->first();

        $pendingJobs = Job::query()
            ->where('client_id', $client->id)
            ->where('payment_status', '!=', JobOperationalPaymentStatus::RECEIVED->value)
            ->orderByDesc('scheduled_date')
            ->get(['id', 'scheduled_date', 'payment_status', 'charges']);

        $lastJob = Job::query()
            ->where('client_id', $client->id)
            ->where('id', '!=', $excludeJobId)
            ->whereNotNull('scheduled_date')
            ->with('assignedEmployees:id,name')
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time')
            ->first();

        return [
            'total_jobs' => (int) ($jobStats->total_jobs ?? 0),
            'completed_jobs' => (int) ($jobStats->completed_jobs ?? 0),
            'pending_amount' => (float) ($jobStats->pending_amount ?? 0),
            'last_job_date' => $lastJob?->scheduled_date?->toDateString(),
            'last_mowers' => $lastJob?->assignedEmployees->pluck('name')->all() ?? [],
            'pending_jobs' => $pendingJobs->map(fn (Job $job): array => [
                'id' => $job->id,
                'scheduled_date' => $job->scheduled_date?->toDateString(),
                'payment_status' => $job->payment_status,
                'charges' => (float) $job->charges,
            ])->all(),
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
