<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Contracts\Reports\MowerReportInterface;
use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\DTOS\Response\Reports\MowerResponseReportDTO;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\ActivityLog;
use App\Models\Job;
use App\Support\QueryFilters\JobListFilter;
use Illuminate\Support\Collection;

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
            ->get();

        return $jobs
            ->groupBy('done_by_user_id')
            ->map(function ($userJobs, $doneByUserId) use ($request) {
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
                    ->sum(fn($job) => $job->charges ?? 0);

                $totalOnlineEarned = $userJobs
                    ->where('payment_mode', $online)
                    ->where('payment_status', $received)
                    ->sum(fn($job) => $job->charges ?? 0);

                $totalSales = $userJobs
                    ->where('payment_status', $received)
                    ->sum(fn($job) => $job->charges ?? 0);

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

                return (new MowerResponseReportDTO())
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
                    ->toArray();
            })
            ->sortByDesc(fn($dto) => $dto['total_sales'])
            ->values();
    }

    public function export(MowerRequestReportDTO $request): \Symfony\Component\HttpFoundation\Response
    {
        $reportData = $this->generate($request);
        return $this->mowerReportExcelExporter->export($reportData);
    }
}
