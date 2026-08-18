<?php

namespace App\Services;

use App\Enums\JobOperationalPaymentStatus;
use App\Models\Job;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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
     * Payload for the Customer Details management modal (single profile + history + totals).
     *
     * @return array{
     *     job: Job,
     *     next_date: string|null,
     *     service_history: array<int, array{id: int, date: string|null, charges: float, mower: string, payment_status: string|null, is_paid: bool, is_verified: bool}>,
     *     total_revenue: float,
     *     payment_received: float,
     *     balance_due: float
     * }
     */
    public function forCustomerDetailsModal(Job $job): array
    {
        $job->loadMissing([
            'zone:id,name',
            'accountingLevel:id,name',
            'clientRating:id,name',
            'recurrence:id,name',
            'doneByUser:id,name',
            'assignedEmployees:id,name',
        ]);

        $historyJobs = $this->relatedJobsQuery($job)
            ->with(['doneByUser:id,name', 'assignedEmployees:id,name'])
            ->whereNotNull('scheduled_date')
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time')
            ->limit(20)
            ->get();

        $totalRevenue = (float) $historyJobs->sum(fn (Job $j) => (float) ($j->charges ?? 0));
        $paymentReceived = (float) $historyJobs
            ->where('payment_status', JobOperationalPaymentStatus::RECEIVED->value)
            ->sum(fn (Job $j) => (float) ($j->charges ?? 0));
        $balanceDue = round($totalRevenue - $paymentReceived, 2);

        // Past visits only — current job payment is managed in the sidebar.
        $serviceHistory = $historyJobs
            ->where('id', '!=', $job->id)
            ->map(function (Job $related): array {
                $mower = $related->doneByUser?->name
                    ?? $related->assignedEmployees->pluck('name')->filter()->first()
                    ?? '—';

                return [
                    'id' => $related->id,
                    'date' => $related->scheduled_date?->format('d M Y'),
                    'charges' => (float) ($related->charges ?? 0),
                    'mower' => $mower,
                    'payment_status' => $related->payment_status,
                    'is_paid' => $related->payment_status === JobOperationalPaymentStatus::RECEIVED->value,
                    'is_verified' => $related->verified_at !== null,
                ];
            })->values()->all();

        $nextDate = null;
        if ($job->scheduled_date && $job->recurrence) {
            $resolved = $job->recurrence->resolve(Carbon::parse($job->scheduled_date));
            $nextDate = $resolved?->format('d M Y');
        }
        if ($nextDate === null && $job->scheduled_date) {
            $nextDate = $job->scheduled_date->format('d M Y');
        }

        return [
            'job' => $job,
            'next_date' => $nextDate,
            'service_history' => $serviceHistory,
            'total_revenue' => $totalRevenue,
            'payment_received' => $paymentReceived,
            'balance_due' => max(0, $balanceDue),
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
