<?php

namespace App\Console\Commands;

use App\Enums\JobOperationalPaymentStatus;
use App\Models\Job;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:fix-verified-jobs-payment-status
    {--dry-run : Preview affected jobs without changing anything}')]
#[Description('One-off catch-up: recompute payment_status (Partial/Received) for jobs verified with a recorded verified_amount, now that verifying a job sets payment_status automatically. Jobs verified without a verified_amount (e.g. historical CRM imports) are left untouched since there is nothing to compare against charges. Safe to re-run.')]
class FixVerifiedJobsPaymentStatus extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $jobs = Job::query()->whereNotNull('verified_at')->whereNotNull('verified_amount')->get();

        $mismatched = $jobs->filter(fn (Job $job): bool => $job->payment_status !== $this->expectedStatus($job));

        if ($mismatched->isEmpty()) {
            $this->info("All {$jobs->count()} verified job(s) with a recorded amount already have the correct payment status.");

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[Dry run] ' : '')."Found {$mismatched->count()} verified job(s) with an incorrect payment status.");

        $fixed = 0;

        $this->withProgressBar($mismatched, function (Job $job) use ($dryRun, &$fixed): void {
            $expected = $this->expectedStatus($job);

            if (! $dryRun) {
                $job->update(['payment_status' => $expected]);
            }

            $fixed++;
        });

        $this->newLine(2);
        $this->info(($dryRun ? 'Would update' : 'Updated')." {$fixed} job(s).");

        return self::SUCCESS;
    }

    private function expectedStatus(Job $job): string
    {
        $jobAmount = (float) ($job->charges ?? 0);
        $verifiedAmount = (float) ($job->verified_amount ?? 0);

        return $verifiedAmount < $jobAmount
            ? JobOperationalPaymentStatus::PARTIAL->value
            : JobOperationalPaymentStatus::RECEIVED->value;
    }
}
