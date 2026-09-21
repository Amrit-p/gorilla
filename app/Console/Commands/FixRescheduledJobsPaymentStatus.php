<?php

namespace App\Console\Commands;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:fix-rescheduled-jobs-payment-status
    {--dry-run : Preview affected jobs without changing anything}')]
#[Description('One-off catch-up: reset payment_status to Pending on auto-rescheduled jobs that inherited Received/Partial from the job they were created from. Only touches jobs that are not completed, not verified and have no payment amounts recorded, so a payment the mower actually logged is never undone. Safe to re-run.')]
class FixRescheduledJobsPaymentStatus extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $jobs = Job::query()
            ->whereNotNull('rescheduled_from_job_id')
            ->where('status', '!=', JobWorkflowStatus::COMPLETED->value)
            ->whereNull('verified_at')
            ->where('payment_status', '!=', JobOperationalPaymentStatus::PENDING->value)
            ->whereNull('received_amount')
            ->whereNull('first_payment')
            ->whereNull('second_payment')
            ->get();

        if ($jobs->isEmpty()) {
            $this->info('No rescheduled jobs carry an inherited payment status.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[Dry run] ' : '')."Found {$jobs->count()} rescheduled job(s) with an inherited payment status.");

        $this->table(
            ['Job', 'Customer', 'Scheduled', 'Status', 'Payment', 'Created from'],
            $jobs->map(fn (Job $job): array => [
                $job->id,
                $job->customerDisplayName(),
                $job->scheduled_date?->toDateString(),
                $job->status,
                $job->payment_status,
                $job->rescheduled_from_job_id,
            ])->all()
        );

        if (! $dryRun) {
            $jobs->each(fn (Job $job): bool => $job->update(['payment_status' => JobOperationalPaymentStatus::PENDING->value]));
        }

        $this->info(($dryRun ? 'Would reset' : 'Reset')." {$jobs->count()} job(s) to Pending payment.");

        return self::SUCCESS;
    }
}
