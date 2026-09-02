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
#[Description('One-off catch-up: create the missing next occurrence for completed recurring jobs that were never auto-rescheduled (e.g. jobs completed while a recurrence pattern failed to parse, or stuck with a stale rescheduled_at from a next job removed outside the normal Hold/Pending flow). Limited to jobs scheduled after --after to avoid recreating old, no-longer-relevant backlog. Safe to re-run.')]
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

        $reconciled = $this->reconcileStuckRescheduledAt($dryRun, $after);

        if ($reconciled > 0) {
            $this->info(
                ($dryRun ? '[Dry run] Would clear' : 'Cleared')
                ." rescheduled_at on {$reconciled} job(s) scheduled after {$after} whose next occurrence no longer exists."
            );

            if ($dryRun) {
                $this->line('Run again without --dry-run to clear those, then run once more to create their next occurrence.');
            }

            $this->newLine();
        }

        $jobs = Job::query()
            ->where('status', JobWorkflowStatus::COMPLETED->value)
            ->where('is_recurring', true)
            ->whereNotNull('recurrence_id')
            ->whereNull('rescheduled_at')
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

    /**
     * Completed jobs stuck with a stale rescheduled_at whose linked next
     * occurrence no longer exists (e.g. deleted outside the normal
     * Hold/Pending flow) are invisible to the query above — nothing would
     * ever pick them up again. Clear the marker so they're eligible.
     */
    private function reconcileStuckRescheduledAt(bool $dryRun, string $after): int
    {
        $liveNextOccurrenceSourceIds = Job::query()
            ->whereNotNull('rescheduled_from_job_id')
            ->pluck('rescheduled_from_job_id');

        $stuckJobs = Job::query()
            ->where('status', JobWorkflowStatus::COMPLETED->value)
            ->where('is_recurring', true)
            ->whereNotNull('recurrence_id')
            ->whereNotNull('rescheduled_at')
            ->whereDate('scheduled_date', '>', $after)
            ->whereNotIn('id', $liveNextOccurrenceSourceIds)
            ->get(['id']);

        if ($stuckJobs->isEmpty()) {
            return 0;
        }

        if (! $dryRun) {
            Job::query()->whereIn('id', $stuckJobs->pluck('id'))->update(['rescheduled_at' => null]);
        }

        return $stuckJobs->count();
    }
}
