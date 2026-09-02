<?php

namespace App\Console\Commands;

use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Services\JobReschedulingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:backfill-job-reschedule {--dry-run : Preview affected jobs without creating anything}')]
#[Description('One-off catch-up: create the missing next occurrence for completed recurring jobs that were never auto-rescheduled (e.g. jobs completed while a recurrence pattern failed to parse). Safe to re-run — only touches jobs still missing rescheduled_at.')]
class BackfillJobReschedule extends Command
{
    public function __construct(private readonly JobReschedulingService $jobReschedulingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $jobs = Job::query()
            ->where('status', JobWorkflowStatus::COMPLETED->value)
            ->where('is_recurring', true)
            ->whereNotNull('recurrence_id')
            ->whereNull('rescheduled_at')
            ->with('recurrence:id,name')
            ->get();

        if ($jobs->isEmpty()) {
            $this->info('No completed recurring jobs are missing their next occurrence.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[Dry run] ' : '')."Found {$jobs->count()} completed job(s) missing their next occurrence.");

        $created = 0;
        $skipped = [];

        $this->withProgressBar($jobs, function (Job $job) use ($dryRun, &$created, &$skipped): void {
            if ($dryRun) {
                $next = $job->recurrence?->resolve($job->scheduled_date->copy());
                $next !== null ? $created++ : $skipped[] = "Job #{$job->id}: recurrence \"{$job->recurrence?->name}\" could not be resolved.";

                return;
            }

            if ($this->jobReschedulingService->rescheduleCompletedJob($job) !== null) {
                $created++;

                return;
            }

            $skipped[] = "Job #{$job->id}: recurrence \"{$job->recurrence?->name}\" could not be resolved.";
        });

        $this->newLine(2);
        $this->info(($dryRun ? 'Would create' : 'Created')." {$created} job(s).");

        if ($skipped !== []) {
            $this->warn(count($skipped).' job(s) skipped:');
            foreach (array_slice($skipped, 0, 20) as $line) {
                $this->line("  - {$line}");
            }
            if (count($skipped) > 20) {
                $this->line('  ... and '.(count($skipped) - 20).' more.');
            }
        }

        return self::SUCCESS;
    }
}
