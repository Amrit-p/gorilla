<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Contracts\Reports\MowerReportInterface;
use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\DTOS\Response\Reports\MowerResponseReportDTO;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\MowerPayout;
use App\Support\QueryFilters\JobListFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class MowerReportService implements MowerReportInterface
{
    public function __construct(
        private readonly JobListFilter $jobListFilter,
        private readonly MowerReportExcelService $mowerReportExcelExporter
    ) {}

    public function generate(MowerRequestReportDTO $request): Collection
    {
        $jobSub = Job::query();
        $jobs = $this->jobListFilter->apply($jobSub, $request->toArray())
            ->whereNotNull('done_by_user_id')
            ->get();

        $userIds = $jobs->pluck('done_by_user_id')->unique()->values()->toArray();
        $jobIds = $jobs->pluck('id')->all();

        $payoutsByUser = $this->payoutsSettlingJobs($userIds, $jobIds)
            ->get(['id', 'user_id', 'amount', 'bonus', 'created_at'])
            ->groupBy('user_id');

        return $jobs
            ->groupBy('done_by_user_id')
            ->map(function ($userJobs, $doneByUserId) use ($payoutsByUser) {
                $user = $userJobs->first()->doneByUser;
                $completed = JobWorkflowStatus::COMPLETED;
                $cash = JobOperationalPaymentMode::CASH;
                $online = JobOperationalPaymentMode::ONLINE;
                $received = JobOperationalPaymentStatus::RECEIVED;

                $completedJobs = $userJobs->where('status', $completed);
                $startedJobs = $userJobs->where('status', JobWorkflowStatus::STARTED);
                $pendingJobs = $userJobs->where('status', JobWorkflowStatus::HOLD);

                $completedEarnings = $completedJobs->sum(fn ($job) => $job->charges ?? 0);
                $startedEarnings = $startedJobs->sum(fn ($job) => $job->charges ?? 0);
                $pendingEarnings = $pendingJobs->sum(fn ($job) => $job->charges ?? 0);

                $totalCashEarned = $userJobs
                    ->where('payment_mode', $cash)
                    ->where('payment_status', $received)
                    ->sum(fn ($job) => $job->charges ?? 0);

                $totalOnlineEarned = $userJobs
                    ->where('payment_mode', $online)
                    ->where('payment_status', $received)
                    ->sum(fn ($job) => $job->charges ?? 0);

                $totalSales = $userJobs
                    ->where('payment_status', $received)
                    ->sum(fn ($job) => $job->charges ?? 0);

                $totalIncentiveAmount = $userJobs
                    ->where('payment_status', $received)
                    ->sum(function ($job) {
                        return (($job->charges ?? 0) * ($job->incentive_percentage ?? 0)) / 100;
                    });

                $totalWorkingHours = $userJobs
                    ->where('status', $completed)
                    ->sum(function ($job) {
                        return $job->consumed_time_minutes ? $job->consumed_time_minutes / 60 : 0;
                    });

                $workingDays = $completedJobs
                    ->pluck('scheduled_date')
                    ->filter()
                    ->map(fn ($d) => Carbon::parse($d)->toDateString())
                    ->unique()
                    ->count();

                $userPayouts = $payoutsByUser->get($doneByUserId, collect());
                $bonusTotal = (float) $userPayouts->sum(fn ($payout) => (float) $payout->bonus);
                $payoutTotal = (float) $userPayouts->sum(fn ($payout) => (float) $payout->amount);

                return (new MowerResponseReportDTO)
                    ->withUserId((int) $doneByUserId)
                    ->withName($user?->name ?? 'Unknown')
                    ->withTotalJobsCompleted($completedJobs->count())
                    ->withTotalJobsStarted($startedJobs->count())
                    ->withTotalJobsPending($pendingJobs->count())
                    ->withCompletedEarnings((float) $completedEarnings)
                    ->withStartedEarnings((float) $startedEarnings)
                    ->withPendingEarnings((float) $pendingEarnings)
                    ->withTotalCashEarned((int) $totalCashEarned)
                    ->withTotalOnlineEarned((int) $totalOnlineEarned)
                    ->withTotalSales((float) $totalSales)
                    ->withTotalIncentiveAmount($totalIncentiveAmount)
                    ->withTotalWorkingHours($totalWorkingHours)
                    ->withWorkingDays($workingDays)
                    ->withTotalBonus($bonusTotal)
                    ->withTotalPayout($payoutTotal)
                    ->withIndividualBonuses(
                        $userPayouts
                            ->groupBy(fn ($payout) => $payout->created_at->format('Y-m-d'))
                            ->map(fn ($dayPayouts) => (float) $dayPayouts->sum(fn ($payout) => (float) $payout->bonus))
                            ->toArray()
                    )
                    ->toArray();
            })
            ->sortByDesc(fn ($dto) => $dto['total_sales'])
            ->values();
    }

    /**
     * The payouts behind one mower's payout and bonus totals, i.e. those settling
     * the jobs the report's filters selected, newest first. Feeds the payouts
     * modal shared with the salary calculator.
     *
     * @return Collection<int, MowerPayout>
     */
    public function payoutsForMower(MowerRequestReportDTO $request, int $userId): Collection
    {
        $jobIds = $this->jobListFilter->apply(Job::query(), $request->toArray())
            ->where('done_by_user_id', $userId)
            ->pluck('id')
            ->all();

        if ($jobIds === []) {
            return collect();
        }

        return $this->payoutsSettlingJobs([$userId], $jobIds)
            ->with('creator:id,name')
            ->withCount('jobs')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * A mower's payout and bonus totals come from the payouts that settle the jobs
     * the report is showing, so both figures and the drill-down stay in step with
     * the filters, whose date range applies to the jobs rather than the payouts.
     *
     * @param  array<int, int|string>  $userIds
     * @param  array<int, int>  $jobIds
     * @return Builder<MowerPayout>
     */
    private function payoutsSettlingJobs(array $userIds, array $jobIds): Builder
    {
        return MowerPayout::query()
            ->whereIn('user_id', $userIds)
            ->whereHas('jobs', fn ($query) => $query->whereIn('service_jobs.id', $jobIds));
    }

    public function export(MowerRequestReportDTO $request): Response
    {
        $reportData = $this->generate($request);

        return $this->mowerReportExcelExporter->export($reportData, $request->hideBonusColumn);
    }
}
