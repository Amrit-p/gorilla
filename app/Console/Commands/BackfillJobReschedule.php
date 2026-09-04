<?php

namespace App\Console\Commands;

use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Services\JobReschedulingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('app:backfill-job-reschedule
    {--dry-run : Preview affected jobs without creating anything}
    {--after=2026-09-01 : Only backfill completed jobs scheduled after this date (Y-m-d)}')]
#[Description('One-off catch-up: create the missing next occurrence for completed recurring jobs that were never auto-rescheduled (e.g. jobs completed while a recurrence pattern failed to parse). Limited to jobs scheduled after --after to avoid recreating old, no-longer-relevant backlog. Safe to re-run.')]
class BackfillJobReschedule extends Command
{
    public function __construct(private readonly JobReschedulingService $jobReschedulingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        try {
            $after = Carbon::parse((string) $this->option('after'))->toDateString();
        } catch (\Throwable) {
            $this->error('Invalid --after date: "'.$this->option('after').'". Use Y-m-d format, e.g. 2026-09-01.');

            return self::FAILURE;
        }

        $jobs = Job::query()
            ->where('status', JobWorkflowStatus::COMPLETED->value)
            ->whereNotNull('recurrence_id')
            ->whereDoesntHave('nextOccurrence')
            ->whereDate('scheduled_date', '>', $after)
            ->with('recurrence')
            ->get();

        if ($jobs->isEmpty()) {
            $this->info("No completed recurring jobs scheduled after {$after} are missing their next occurrence.");

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[Dry run] ' : '')."Found {$jobs->count()} completed job(s) scheduled after {$after} missing their next occurrence.");

        $created = 0;
        $skipped = [];

        $this->withProgressBar($jobs, function (Job $job) use ($dryRun, &$created, &$skipped): void {
            if ($dryRun) {
                $next = $job->recurrence?->resolve($job->scheduled_date->copy());
                $next !== null ? $created++ : $skipped[] = "Job #{$job->id}: recurrence \"{$job->recurrence?->name}\" has no days set.";

                return;
            }

            if ($this->jobReschedulingService->rescheduleCompletedJob($job) !== null) {
                $created++;

                return;
            }

            $skipped[] = "Job #{$job->id}: recurrence \"{$job->recurrence?->name}\" has no days set.";
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
