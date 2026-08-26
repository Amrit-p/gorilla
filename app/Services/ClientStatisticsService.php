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
     * Payload for the Customer Details management modal.
     *
     * Jobs are grouped by address, so the modal lists every job booked at the
     * clicked job's address alongside that job's own payment detail.
     *
     * @return array{
     *     job: Job,
     *     next_date: string|null,
     *     group_address: string,
     *     address_jobs: array<int, array{id: int, is_current: bool, date: string|null, time: string|null, status: string|null, services: string, charges: float, mower: string, payment_mode: string|null, payment_status: string|null, payment_pending_reason: string|null, is_paid: bool, is_verified: bool}>,
     *     total_jobs: int,
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

        $addressJobs = $this->addressGroupQuery($job)
            ->with(['doneByUser:id,name', 'assignedEmployees:id,name'])
            ->orderByRaw('scheduled_date IS NULL')
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time')
            ->orderByDesc('id')
            ->get();

        $totalRevenue = (float) $addressJobs->sum(fn (Job $related) => (float) ($related->charges ?? 0));
        $paymentReceived = (float) $addressJobs
            ->where('payment_status', JobOperationalPaymentStatus::RECEIVED->value)
            ->sum(fn (Job $related) => (float) ($related->charges ?? 0));
        $balanceDue = round($totalRevenue - $paymentReceived, 2);

        return [
            'job' => $job,
            'next_date' => $this->nextDateFor($job),
            'group_address' => trim((string) $job->client_address),
            'address_jobs' => $addressJobs
                ->map(fn (Job $related): array => $this->presentAddressJob($related, $job->id))
                ->values()
                ->all(),
            'total_jobs' => $addressJobs->count(),
            'total_revenue' => $totalRevenue,
            'payment_received' => $paymentReceived,
            'balance_due' => max(0, $balanceDue),
        ];
    }

    /**
     * One row of the modal's job list, carrying that job's own payment detail.
     *
     * @return array{id: int, is_current: bool, date: string|null, time: string|null, status: string|null, services: string, charges: float, mower: string, payment_mode: string|null, payment_status: string|null, payment_pending_reason: string|null, is_paid: bool, is_verified: bool}
     */
    private function presentAddressJob(Job $job, int $currentJobId): array
    {
        return [
            'id' => $job->id,
            'is_current' => $job->id === $currentJobId,
            'date' => $job->scheduled_date?->format('d M Y'),
            'time' => $job->scheduled_time,
            'status' => $job->status,
            'services' => is_array($job->required_services) && $job->required_services !== []
                ? implode(', ', $job->required_services)
                : '—',
            'charges' => (float) ($job->charges ?? 0),
            'mower' => $job->doneByUser?->name
                ?? $job->assignedEmployees->pluck('name')->filter()->first()
                ?? '—',
            'payment_mode' => $job->payment_mode,
            'payment_status' => $job->payment_status,
            'payment_pending_reason' => $job->payment_pending_reason,
            'is_paid' => $job->payment_status === JobOperationalPaymentStatus::RECEIVED->value,
            'is_verified' => $job->verified_at !== null,
        ];
    }

    private function nextDateFor(Job $job): ?string
    {
        if ($job->scheduled_date && $job->recurrence) {
            $resolved = $job->recurrence->resolve(Carbon::parse($job->scheduled_date));
            if ($resolved) {
                return $resolved->format('d M Y');
            }
        }

        return $job->scheduled_date?->format('d M Y');
    }

    /**
     * Every job sharing this job's address. Jobs without an address fall back to
     * the phone/email match so the modal still groups the customer's work.
     *
     * @return Builder<Job>
     */
    private function addressGroupQuery(Job $job): Builder
    {
        $address = mb_strtolower(trim((string) $job->client_address));

        if ($address === '') {
            return $this->relatedJobsQuery($job);
        }

        return Job::query()->whereRaw('LOWER(TRIM(client_address)) = ?', [$address]);
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
