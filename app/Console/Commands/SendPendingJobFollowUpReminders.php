<?php

namespace App\Console\Commands;

use App\Enums\{
    JobWorkflowStatus,
    JobOperationalPaymentStatus,
};
use App\Helpers\OptimizationHelper;
use App\Models\Job;
use App\Models\User;
use App\Notifications\JobPendingFollowUpNotification;
use App\Support\CrmRoles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-pending-job-reminders')]
#[Description('Send follow-up reminders to office managers for jobs that have been pending for 2+ days with no updates.')]
class SendPendingJobFollowUpReminders extends Command
{
    public function handle(): int
    {
        $staleJobs = Job::query()
            ->where(function ($query) {
                $query->whereIn('status', [JobWorkflowStatus::PENDING->value, JobWorkflowStatus::HOLD->value])
                    ->orWhereIn('payment_status', [JobOperationalPaymentStatus::PENDING->value]);
            })
            ->where('updated_at', '<=', now()->subDays(2))
            ->get();

        if ($staleJobs->isEmpty()) {
            $this->info('No stale pending jobs found.');

            return self::SUCCESS;
        }

        $managers = User::query()->role([CrmRoles::OFFICE_MANAGER])->get();

        $managers->each(function (User $manager) use ($staleJobs): void {
            $staleJobs->each(function (Job $job) use ($manager): void {
                $manager->notify(new JobPendingFollowUpNotification($job));
            });
            OptimizationHelper::forgetNotificationUnreadCount($manager->id);
        });

        $this->info("Sent follow-up reminders for {$staleJobs->count()} pending job(s) to {$managers->count()} manager(s).");

        return self::SUCCESS;
    }
}
