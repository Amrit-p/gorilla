<?php

namespace App\Console\Commands;

use App\Enums\JobWorkflowStatus;
use App\Helpers\OptimizationHelper;
use App\Models\Job;
use App\Models\User;
use App\Notifications\JobAutoRescheduledNotification;
use App\Support\CrmRoles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:auto-reschedule-jobs')]
#[Description('Create new recurring jobs for completed jobs based on their recurrence schedule.')]
class AutoRescheduleJobs extends Command
{
    public function handle(): int
    {
        $jobs = Job::query()
            ->with('recurrence')
            ->where('status', JobWorkflowStatus::COMPLETED->value)
            ->where('is_recurring', true)
            ->whereNotNull('recurrence_id')
            ->whereNull('rescheduled_at')
            ->get();

        if ($jobs->isEmpty()) {
            $this->info('No completed recurring jobs to reschedule.');

            return self::SUCCESS;
        }

        $rescheduled = 0;

        $managers = User::query()->role([CrmRoles::OFFICE_MANAGER])->get();

        $jobs->each(function (Job $job) use ($managers, &$rescheduled): void {
            $nextDate = $job->recurrence->resolve(
                $job->scheduled_date->copy()
            );

            if ($nextDate === null) {
                $this->line("Skipping job #{$job->id} (recurrence \"{$job->recurrence->name}\" has no next date).");

                return;
            }

            DB::transaction(function () use ($job, $nextDate, $managers, &$rescheduled): void {
                $newJob = Job::create([
                    'client_id' => $job->client_id,
                    'lead_id' => $job->lead_id,
                    'zone_id' => $job->zone_id,
                    'equipment_type_id' => $job->equipment_type_id,
                    'job_level_id' => $job->job_level_id,
                    'contract_id' => $job->contract_id,
                    'recurrence_id' => $job->recurrence_id,
                    'accounting_level_id' => $job->accounting_level_id,
                    'client_rating_id' => $job->client_rating_id,
                    'customer_name' => $job->customer_name,
                    'email' => $job->email,
                    'phone' => $job->phone,
                    'weed_spray' => $job->weed_spray,
                    'job_type' => $job->job_type,
                    'property_details' => $job->property_details,
                    'notes' => $job->notes,
                    'client_address' => $job->client_address,
                    'latitude' => $job->latitude,
                    'longitude' => $job->longitude,
                    'scheduled_date' => $nextDate->toDateString(),
                    'scheduled_time' => $job->scheduled_time,
                    'estimated_duration_minutes' => $job->estimated_duration_minutes,
                    'required_services' => $job->required_services,
                    'is_recurring' => true,
                    'route_sequence' => $job->route_sequence,
                    'priority' => $job->priority,
                    'numeric_priority' => $job->numeric_priority,
                    'status' => JobWorkflowStatus::PENDING->value,
                    'site_instructions' => $job->site_instructions,
                    'parking_status' => $job->parking_status,
                    'customer_type' => $job->customer_type,
                    'pet_warning' => $job->pet_warning,
                    'charges' => $job->charges,
                    'incentive_percentage' => $job->incentive_percentage,
                    'special_remarks' => $job->special_remarks,
                    'internal_notes' => $job->internal_notes,
                    'payment_mode' => $job->payment_mode,
                    'payment_status' => $job->payment_status,
                    'created_by' => $job->created_by,
                ]);

                $job->update(['rescheduled_at' => now()]);

                $managers->each(function (User $manager) use ($newJob): void {
                    $manager->notify(new JobAutoRescheduledNotification($newJob));
                    OptimizationHelper::forgetNotificationUnreadCount($manager->id);
                });

                if ($job->doneByUser) {
                    $job->doneByUser->notify(new JobAutoRescheduledNotification($newJob));
                    OptimizationHelper::forgetNotificationUnreadCount($job->doneByUser->id);
                }

                $rescheduled++;
            });
        });

        $this->info("Auto-rescheduled {$rescheduled} job(s).");

        return self::SUCCESS;
    }
}
