<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOS\Response\Salary\PayoutMonthRowDTO;
use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\MowerPayout;
use App\Models\User;
use App\Support\CrmRoles;
use App\Support\MowerPayoutLookup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalaryCalculatorService
{
    /** @return Collection<int, User> */
    public function mowers(): Collection
    {
        return User::role(CrmRoles::MOWER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'user_unique_id']);
    }

    /**
     * One row per calendar month of the given year, aggregated from the mower's
     * completed jobs and from the payouts those jobs belong to.
     *
     * @return array<int, PayoutMonthRowDTO>
     */
    public function monthlyBreakdown(User $mower, int $year): array
    {
        $jobTotals = $this->completedJobTotalsByMonth($mower, $year);
        $payoutTotals = $this->payoutTotalsByMonth($mower, $year);

        $rows = [];

        for ($month = 1; $month <= 12; $month++) {
            $jobs = $jobTotals[$month] ?? ['sales' => 0.0, 'minutes' => 0];
            $payouts = $payoutTotals[$month] ?? ['payout' => 0.0, 'bonus' => 0.0, 'count' => 0];

            $rows[] = new PayoutMonthRowDTO(
                mower_id: $mower->id,
                mower_name: $mower->name,
                year: $year,
                month: $month,
                month_label: Carbon::create($year, $month, 1)->format('F'),
                total_sales: round($jobs['sales'], 2),
                total_bonus: round($payouts['bonus'], 2),
                total_payout: round($payouts['payout'], 2),
                total_hours: round($jobs['minutes'] / 60, 2),
                payout_count: $payouts['count'],
            );
        }

        return $rows;
    }

    /**
     * Completed jobs for the mower in the given month, newest first, each flagged
     * with whether a payout already covers it.
     *
     * @return LengthAwarePaginator<int, Job>
     */
    public function jobsForMonth(User $mower, int $year, int $month, int $perPage = 10): LengthAwarePaginator
    {
        [$start, $end] = $this->monthBounds($year, $month);

        return $this->completedJobsQuery($mower)
            ->whereBetween('scheduled_date', [$start->toDateString(), $end->toDateString()])
            ->addSelect(['mower_payout_id' => MowerPayoutLookup::forJob()])
            ->with([
                'clientRating:id,name,description',
                'accountingLevel:id,name',
                'zone:id,name',
                'recurrence:id,name',
                'equipmentType:id,name,color_code',
                'jobLevel:id,name,color_code',
                'assignedEmployees:id,name,efficiency',
                'doneByUser:id,name',
                'verifier:id,name',
                'contract:id,contractor_id,name,start_date,end_date,status',
                'contract.contractor:id,name,phone,email',
            ])
            ->orderByDesc('scheduled_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * The payouts counted against the given month, newest first, each carrying its
     * creator and its linked job count for the "payouts for this month" modal. A
     * payout belongs to the month of its earliest linked job, matching the totals
     * shown in the monthly breakdown.
     *
     * @return \Illuminate\Support\Collection<int, MowerPayout>
     */
    public function payoutsForMonth(User $mower, int $year, int $month): \Illuminate\Support\Collection
    {
        return MowerPayout::query()
            ->where('user_id', $mower->id)
            ->with(['creator:id,name', 'jobs:id,scheduled_date'])
            ->withCount('jobs')
            ->get()
            ->filter(function (MowerPayout $payout) use ($year, $month): bool {
                $earliest = $this->earliestJobDate($payout);

                return $earliest !== null
                    && (int) $earliest->year === $year
                    && (int) $earliest->month === $month;
            })
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * Sale and hour totals for an ad-hoc set of jobs, used to fill the payout modal.
     *
     * @param  array<int, int>  $jobIds
     * @return array{total_sales: float, total_hours: float, job_count: int}
     */
    public function selectionTotals(User $mower, array $jobIds): array
    {
        $row = $this->completedJobsQuery($mower)
            ->whereIn('id', $jobIds)
            ->selectRaw('COUNT(*) as job_count, COALESCE(SUM(charges), 0) as sales, COALESCE(SUM(consumed_time_minutes), 0) as minutes')
            ->first();

        return [
            'total_sales' => round((float) ($row->sales ?? 0), 2),
            'total_hours' => round((float) ($row->minutes ?? 0) / 60, 2),
            'job_count' => (int) ($row->job_count ?? 0),
        ];
    }

    /**
     * Describe an arbitrary job selection (from any jobs view) as a list of
     * payout cards. Unpaid jobs become one "create" card per mower; jobs that
     * are already paid become an "update" card for the payout covering them, so
     * a mixed selection is created and corrected in the same modal.
     *
     * @param  array<int, int>  $jobIds
     * @return array<string, mixed>
     */
    public function payoutSelectionSummary(array $jobIds): array
    {
        $jobIds = array_values(array_unique(array_map('intval', $jobIds)));

        $blank = ['ok' => false, 'error' => null, 'groups' => [], 'job_count' => 0];

        if ($jobIds === []) {
            return [...$blank, 'error' => 'Select at least one job.'];
        }

        $jobs = Job::query()
            ->whereIn('id', $jobIds)
            ->addSelect(['mower_payout_id' => MowerPayoutLookup::forJob()])
            ->with(['doneByUser:id,name'])
            ->get(['id', 'status', 'charges', 'consumed_time_minutes', 'done_by_user_id']);

        if ($jobs->count() !== count($jobIds)) {
            return [...$blank, 'error' => 'Some selected jobs no longer exist.'];
        }

        if ($jobs->contains(fn (Job $job) => $job->status !== JobWorkflowStatus::COMPLETED->value)) {
            return [...$blank, 'error' => 'Only completed jobs can be paid out.'];
        }

        if ($jobs->contains(fn (Job $job) => $job->done_by_user_id === null)) {
            return [...$blank, 'error' => 'Some selected jobs have no mower assigned.'];
        }

        [$paid, $unpaid] = $jobs->partition(fn (Job $job) => $job->mower_payout_id !== null);

        $groups = [
            ...$this->createCards($unpaid),
            ...$this->updateCards($paid),
        ];

        usort($groups, fn (array $a, array $b) => [$a['mode'], $a['mower_name']] <=> [$b['mode'], $b['mower_name']]);

        return [
            'ok' => $groups !== [],
            'error' => $groups === [] ? 'Nothing to pay out for this selection.' : null,
            'groups' => $groups,
            'job_count' => $jobs->count(),
        ];
    }

    /**
     * One "create" card per mower with unpaid jobs in the selection.
     *
     * @param  \Illuminate\Support\Collection<int, Job>  $jobs
     * @return array<int, array<string, mixed>>
     */
    private function createCards($jobs): array
    {
        return $jobs
            ->groupBy('done_by_user_id')
            ->map(function ($mowerJobs) {
                $sales = round((float) $mowerJobs->sum(fn (Job $job) => $job->charges ?? 0), 2);

                return [
                    'mode' => 'create',
                    'key' => 'create-'.$mowerJobs->first()->done_by_user_id,
                    'payout_id' => null,
                    'mower_id' => (int) $mowerJobs->first()->done_by_user_id,
                    'mower_name' => $mowerJobs->first()->doneByUser?->name ?? 'Unknown',
                    'job_ids' => $mowerJobs->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                    'job_count' => $mowerJobs->count(),
                    'selected_job_count' => $mowerJobs->count(),
                    'total_sales' => $sales,
                    'total_hours' => round((float) $mowerJobs->sum(fn (Job $job) => $job->consumed_time_minutes ?? 0) / 60, 2),
                    'amount' => $sales,
                    'bonus' => 0.0,
                    'comment' => null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * One "update" card per existing payout touched by the selection. Its totals
     * cover every job on that payout, not just the ones that were ticked, since
     * the amount being edited pays for all of them.
     *
     * @param  \Illuminate\Support\Collection<int, Job>  $jobs
     * @return array<int, array<string, mixed>>
     */
    private function updateCards($jobs): array
    {
        $selectedByPayout = $jobs->groupBy('mower_payout_id');

        if ($selectedByPayout->isEmpty()) {
            return [];
        }

        return MowerPayout::query()
            ->whereIn('id', $selectedByPayout->keys()->all())
            ->with(['user:id,name', 'jobs:id,charges,consumed_time_minutes'])
            ->get()
            ->map(fn (MowerPayout $payout) => [
                'mode' => 'update',
                'key' => 'update-'.$payout->id,
                'payout_id' => $payout->id,
                'mower_id' => $payout->user_id,
                'mower_name' => $payout->user?->name ?? 'Unknown',
                'job_ids' => $payout->jobs->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'job_count' => $payout->jobs->count(),
                'selected_job_count' => $selectedByPayout->get($payout->id)?->count() ?? 0,
                'total_sales' => round((float) $payout->jobs->sum(fn (Job $job) => $job->charges ?? 0), 2),
                'total_hours' => round((float) $payout->jobs->sum(fn (Job $job) => $job->consumed_time_minutes ?? 0) / 60, 2),
                'amount' => (float) $payout->amount,
                'bonus' => (float) $payout->bonus,
                'comment' => $payout->comment,
            ])
            ->values()
            ->all();
    }

    /**
     * Apply every card the modal submitted - creating new payouts and correcting
     * existing ones - as a single all-or-nothing operation.
     *
     * @param  array<int, array<string, mixed>>  $groups
     * @return array{created: int, updated: int}
     */
    public function savePayouts(array $groups, User $actor): array
    {
        return DB::transaction(function () use ($groups, $actor): array {
            $created = 0;
            $updated = 0;

            foreach ($groups as $group) {
                if (($group['mode'] ?? 'create') === 'update') {
                    $this->updatePayout(
                        MowerPayout::findOrFail($group['payout_id']),
                        (float) $group['amount'],
                        (float) $group['bonus'],
                        $group['comment'] ?? null
                    );
                    $updated++;

                    continue;
                }

                $mowerId = $group['mower_id'] ?? $this->resolveMowerId($group['job_ids']);

                if ($mowerId === null) {
                    throw ValidationException::withMessages([
                        'payouts' => 'Could not work out which mower one of these payouts belongs to.',
                    ]);
                }

                $this->createPayout(
                    User::findOrFail($mowerId),
                    $group['job_ids'],
                    (float) $group['amount'],
                    (float) $group['bonus'],
                    $group['comment'] ?? null,
                    $actor
                );

                $created++;
            }

            return ['created' => $created, 'updated' => $updated];
        });
    }

    /** Correct an existing payout. The jobs it covers are not changed here. */
    public function updatePayout(
        MowerPayout $payout,
        float $amount,
        float $bonus,
        ?string $comment
    ): MowerPayout {
        $payout->update([
            'amount' => $amount,
            'bonus' => $bonus,
            'comment' => $comment,
        ]);

        return $payout->refresh();
    }

    /**
     * Remove a payout entirely. Deleting the pivot rows releases its jobs, so
     * they become selectable for a fresh payout again.
     */
    public function deletePayout(MowerPayout $payout): void
    {
        DB::transaction(function () use ($payout): void {
            $payout->jobs()->detach();
            $payout->delete();
        });
    }

    /**
     * The single mower a set of jobs was done by, or null when they span several.
     *
     * @param  array<int, int>  $jobIds
     */
    public function resolveMowerId(array $jobIds): ?int
    {
        $mowerIds = Job::query()
            ->whereIn('id', $jobIds)
            ->pluck('done_by_user_id')
            ->filter()
            ->unique();

        return $mowerIds->count() === 1 ? (int) $mowerIds->first() : null;
    }

    /**
     * Record one payout and attach the selected jobs to it.
     *
     * @param  array<int, int>  $jobIds
     */
    public function createPayout(
        User $mower,
        array $jobIds,
        float $amount,
        float $bonus,
        ?string $comment,
        User $actor
    ): MowerPayout {
        $jobIds = array_values(array_unique(array_map('intval', $jobIds)));

        $this->assertJobsArePayable($mower, $jobIds);

        return DB::transaction(function () use ($mower, $jobIds, $amount, $bonus, $comment, $actor): MowerPayout {
            $payout = MowerPayout::create([
                'user_id' => $mower->id,
                'amount' => $amount,
                'bonus' => $bonus,
                'comment' => $comment,
                'created_by' => $actor->id,
            ]);

            $payout->jobs()->attach($jobIds);

            return $payout;
        });
    }

    /**
     * Every selected job must belong to this mower, be completed, and not already
     * be covered by a payout - otherwise the mower would be paid twice for it.
     *
     * @param  array<int, int>  $jobIds
     */
    private function assertJobsArePayable(User $mower, array $jobIds): void
    {
        if ($jobIds === []) {
            throw ValidationException::withMessages([
                'job_ids' => 'Select at least one completed job to pay out.',
            ]);
        }

        $payable = $this->completedJobsQuery($mower)
            ->withoutMowerPayout()
            ->whereIn('id', $jobIds)
            ->pluck('id')
            ->all();

        $rejected = array_diff($jobIds, $payable);

        if ($rejected !== []) {
            throw ValidationException::withMessages([
                'job_ids' => 'Some selected jobs are already paid out, or are not completed jobs for this mower.',
            ]);
        }
    }

    /** @return Builder<Job> */
    private function completedJobsQuery(User $mower): Builder
    {
        return Job::query()
            ->where('done_by_user_id', $mower->id)
            ->where('status', JobWorkflowStatus::COMPLETED->value);
    }

    /**
     * Grouped in PHP rather than with MONTH() so the query stays portable across
     * the MySQL app database and the SQLite database the test suite runs on.
     *
     * @return array<int, array{sales: float, minutes: int}> keyed by month number
     */
    private function completedJobTotalsByMonth(User $mower, int $year): array
    {
        $totals = [];

        $this->completedJobsQuery($mower)
            ->whereYear('scheduled_date', $year)
            ->get(['scheduled_date', 'charges', 'consumed_time_minutes'])
            ->each(function (Job $job) use (&$totals): void {
                $month = (int) Carbon::parse($job->scheduled_date)->month;

                $totals[$month] ??= ['sales' => 0.0, 'minutes' => 0];
                $totals[$month]['sales'] += (float) ($job->charges ?? 0);
                $totals[$month]['minutes'] += (int) ($job->consumed_time_minutes ?? 0);
            });

        return $totals;
    }

    /**
     * A payout can in principle span months, so each one is counted once against
     * the month of its earliest linked job rather than in every month it touches.
     *
     * @return array<int, array{payout: float, bonus: float, count: int}> keyed by month number
     */
    private function payoutTotalsByMonth(User $mower, int $year): array
    {
        $payouts = MowerPayout::query()
            ->where('user_id', $mower->id)
            ->with(['jobs:id,scheduled_date'])
            ->get();

        $totals = [];

        foreach ($payouts as $payout) {
            $earliest = $this->earliestJobDate($payout);

            if ($earliest === null || (int) $earliest->year !== $year) {
                continue;
            }

            $month = (int) $earliest->month;
            $totals[$month] ??= ['payout' => 0.0, 'bonus' => 0.0, 'count' => 0];
            $totals[$month]['payout'] += (float) $payout->amount;
            $totals[$month]['bonus'] += (float) $payout->bonus;
            $totals[$month]['count']++;
        }

        return $totals;
    }

    /** The date of the payout's earliest linked job, or null when it has none. */
    private function earliestJobDate(MowerPayout $payout): ?Carbon
    {
        return $payout->jobs
            ->pluck('scheduled_date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date))
            ->sort()
            ->first();
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function monthBounds(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();

        return [$start, $start->copy()->endOfMonth()];
    }
}
