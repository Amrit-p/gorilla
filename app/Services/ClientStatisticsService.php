<?php

namespace App\Services;

use App\Enums\JobOperationalPaymentStatus;
use App\Models\Job;
use Illuminate\Database\Eloquent\Builder;

class ClientStatisticsService
{
    /**
     * Sidebar stats for related jobs matched by phone (or email fallback).
     *
     * @return array{total_jobs: int, completed_jobs: int, pending_amount: float, last_job_date: string|null, last_mowers: array<int, string>, pending_jobs: array<int, array{id: int, scheduled_date: string|null, payment_status: string|null, charges: float}>}
     */
    public function forJobShowSidebar(Job $job, int $excludeJobId): array
    {
        $related = $this->relatedJobsQuery($job);

        $jobStats = (clone $related)
            ->selectRaw('
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_jobs,
                SUM(CASE WHEN payment_status != ? THEN charges ELSE 0 END) as pending_amount
            ', ['Completed', JobOperationalPaymentStatus::RECEIVED->value])
            ->first();

        $pendingJobs = (clone $related)
            ->where('payment_status', '!=', JobOperationalPaymentStatus::RECEIVED->value)
            ->orderByDesc('scheduled_date')
            ->get(['id', 'scheduled_date', 'payment_status', 'charges']);

        $lastJob = (clone $related)
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
            'pending_jobs' => $pendingJobs->map(fn (Job $relatedJob): array => [
                'id' => $relatedJob->id,
                'scheduled_date' => $relatedJob->scheduled_date?->toDateString(),
                'payment_status' => $relatedJob->payment_status,
                'charges' => (float) $relatedJob->charges,
            ])->all(),
        ];
    }

    /**
     * @return Builder<Job>
     */
    private function relatedJobsQuery(Job $job): Builder
    {
        $phone = trim((string) $job->phone);
        $email = trim((string) $job->email);

        return Job::query()
            ->when(
                $phone !== '',
                fn (Builder $query) => $query->where('phone', $phone),
                fn (Builder $query) => $query->when(
                    $email !== '',
                    fn (Builder $inner) => $inner->where('email', $email),
                    fn (Builder $inner) => $inner->whereKey($job->id)
                )
            );
    }
}
