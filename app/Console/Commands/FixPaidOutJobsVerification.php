<?php

namespace App\Console\Commands;

use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\MowerPayout;
use App\Models\User;
use App\Services\JobManagementService;
use App\Services\SalaryCalculatorService;
use App\Support\CrmConstants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

#[Signature('app:fix-paid-out-jobs-verification
    {--dry-run : Preview affected jobs without changing anything}')]
#[Description('One-off catch-up for payouts recorded before paying a mower out verified the job. Completed paid-out jobs are verified as the payout creator, dated the day of the payout. Paid-out jobs since walked back from Completed now stand for a new, unpaid visit, so they are released from their payout, and a payout left with no jobs is deleted. Payout amounts are never changed. Safe to re-run.')]
class FixPaidOutJobsVerification extends Command
{
    public function __construct(
        private readonly SalaryCalculatorService $salaryCalculatorService,
        private readonly JobManagementService $jobManagementService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $unverified = Job::query()
            ->withMowerPayout()
            ->where('status', JobWorkflowStatus::COMPLETED->value)
            ->whereNull('verified_at')
            ->with('payouts.creator')
            ->orderBy('id')
            ->get();

        $unfinished = Job::query()
            ->withMowerPayout()
            ->where('status', '!=', JobWorkflowStatus::COMPLETED->value)
            ->with('payouts')
            ->orderBy('id')
            ->get();

        if ($unverified->isEmpty() && $unfinished->isEmpty()) {
            $this->info('Every paid-out job is already Completed and verified.');

            return self::SUCCESS;
        }

        $verifications = $this->planVerifications($unverified);

        if (collect($verifications)->contains(fn (array $verification): bool => $verification['verifier'] === null)) {
            $this->error('Some payouts have no creator and the admin account ('.CrmConstants::ADMIN_EMAIL.') was not found, so there is no one to verify their jobs as.');

            return self::FAILURE;
        }

        $emptiedPayoutIds = $this->payoutsLeftEmpty($unfinished);

        $this->reportVerifications($verifications, $dryRun);
        $this->reportReleases($unfinished, $emptiedPayoutIds, $dryRun);

        if ($dryRun) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($verifications, $unfinished): void {
            foreach ($verifications as $verification) {
                $this->salaryCalculatorService->verifyPaidJobs(
                    $verification['verifier'],
                    $verification['jobs']->pluck('id')->all(),
                    $verification['date']
                );
            }

            $unfinished->each(fn (Job $job): array => $this->jobManagementService->releaseFromMowerPayouts($job));
        });

        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * One verification per payout, made by whoever recorded the payout (or the
     * admin account when that user is gone) and dated the day it was recorded.
     *
     * @param  Collection<int, Job>  $jobs
     * @return array<int, array{payout: MowerPayout, verifier: User|null, date: string, jobs: Collection<int, Job>}>
     */
    private function planVerifications(Collection $jobs): array
    {
        $fallbackVerifier = User::query()->where('email', CrmConstants::ADMIN_EMAIL)->first();

        return $jobs
            ->groupBy(fn (Job $job): int => $job->payouts->first()->id)
            ->map(function (Collection $payoutJobs) use ($fallbackVerifier): array {
                $payout = $payoutJobs->first()->payouts->first();

                return [
                    'payout' => $payout,
                    'verifier' => $payout->creator ?? $fallbackVerifier,
                    'date' => $payout->created_at->toDateString(),
                    'jobs' => $payoutJobs,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Payouts whose every job is about to be released, which the release deletes.
     *
     * @param  Collection<int, Job>  $releasing
     * @return array<int, int>
     */
    private function payoutsLeftEmpty(Collection $releasing): array
    {
        $releasingIds = $releasing->pluck('id');

        return MowerPayout::query()
            ->whereIn('id', $releasing->flatMap(fn (Job $job) => $job->payouts->pluck('id'))->unique()->all())
            ->with(['jobs' => fn ($query) => $query->withTrashed()])
            ->get()
            ->filter(fn (MowerPayout $payout): bool => $payout->jobs->pluck('id')->diff($releasingIds)->isEmpty())
            ->pluck('id')
            ->all();
    }

    /**
     * @param  array<int, array{payout: MowerPayout, verifier: User|null, date: string, jobs: Collection<int, Job>}>  $verifications
     */
    private function reportVerifications(array $verifications, bool $dryRun): void
    {
        $count = array_sum(array_map(fn (array $verification): int => $verification['jobs']->count(), $verifications));

        if ($count === 0) {
            return;
        }

        $this->info(($dryRun ? '[Dry run] Would verify' : 'Verifying')." {$count} Completed paid-out job(s):");

        $this->table(
            ['Job', 'Customer', 'Scheduled', 'Payout', 'Verified by', 'Verified date'],
            collect($verifications)->flatMap(fn (array $verification) => $verification['jobs']->map(fn (Job $job): array => [
                $job->id,
                $job->customerDisplayName(),
                $job->scheduled_date?->toDateString(),
                '#'.$verification['payout']->id,
                $verification['verifier']?->name ?? '—',
                $verification['date'],
            ]))->all()
        );
    }

    /**
     * @param  Collection<int, Job>  $jobs
     * @param  array<int, int>  $emptiedPayoutIds
     */
    private function reportReleases(Collection $jobs, array $emptiedPayoutIds, bool $dryRun): void
    {
        if ($jobs->isEmpty()) {
            return;
        }

        $this->info(($dryRun ? '[Dry run] Would release' : 'Releasing')." {$jobs->count()} paid-out job(s) that are no longer Completed:");

        $this->table(
            ['Job', 'Customer', 'Scheduled', 'Status', 'Payout', 'Payout afterwards'],
            $jobs->map(function (Job $job) use ($emptiedPayoutIds): array {
                $payout = $job->payouts->first();

                return [
                    $job->id,
                    $job->customerDisplayName(),
                    $job->scheduled_date?->toDateString(),
                    $job->status,
                    '#'.$payout->id.' ($'.number_format($payout->totalAmount(), 2).')',
                    in_array($payout->id, $emptiedPayoutIds, true) ? 'Deleted - no jobs left' : 'Kept, amount unchanged',
                ];
            })->all()
        );

        if ($jobs->contains(fn (Job $job): bool => ! in_array($job->payouts->first()->id, $emptiedPayoutIds, true))) {
            $this->warn('Kept payouts still carry their original amount - correct it from the "Mower paid" badge if it included a released job.');
        }
    }
}
