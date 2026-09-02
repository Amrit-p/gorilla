<?php

namespace App\Services;

use App\Enums\JobWorkflowStatus;
use App\Helpers\OptimizationHelper;
use App\Models\Job;
use App\Models\User;
use App\Notifications\JobAutoRescheduledNotification;
use App\Support\CrmRoles;
use Illuminate\Support\Facades\DB;

class JobReschedulingService
{
    /**
     * Create the next recurring occurrence for a job that has just been completed.
     *
     * Returns the newly-created job, or null when there is nothing to schedule
     * (non-recurring, no recurrence, already rescheduled, or a one-time recurrence).
     */
    public function rescheduleCompletedJob(Job $job): ?Job
    {
        // Read from a fresh snapshot so every column (including DB defaults) is present,
        // regardless of how fully hydrated the instance that changed status was.
        $source = $job->fresh();

        if ($source === null
            || ! $source->is_recurring
            || $source->recurrence_id === null
            || $source->rescheduled_at !== null) {
            return null;
        }

        $source->loadMissing('recurrence');

        if ($source->recurrence === null) {
            return null;
        }

        $nextDate = $source->recurrence->resolve($source->scheduled_date->copy());

        if ($nextDate === null) {
            return null;
        }

        return DB::transaction(function () use ($job, $source, $nextDate): Job {
            $newJob = Job::create([
                'lead_id' => $source->lead_id,
                'zone_id' => $source->zone_id,
                'equipment_type_id' => $source->equipment_type_id,
                'job_level_id' => $source->job_level_id,
                'contract_id' => $source->contract_id,
                'recurrence_id' => $source->recurrence_id,
                'accounting_level_id' => $source->accounting_level_id,
                'client_rating_id' => $source->client_rating_id,
                'customer_name' => $source->customer_name,
                'email' => $source->email,
                'phone' => $source->phone,
                'weed_spray' => $source->weed_spray,
                'job_type' => $source->job_type,
                'property_details' => $source->property_details,
                'notes' => $source->notes,
                'client_address' => $source->client_address,
                'latitude' => $source->latitude,
                'longitude' => $source->longitude,
                'scheduled_date' => $nextDate->toDateString(),
                'scheduled_time' => $source->scheduled_time,
                'estimated_duration_minutes' => $source->estimated_duration_minutes,
                'required_services' => $source->required_services,
                'is_recurring' => true,
                'route_sequence' => $source->route_sequence,
                'priority' => $source->priority,
                'numeric_priority' => $source->numeric_priority,
                'status' => JobWorkflowStatus::PENDING->value,
                'site_instructions' => $source->site_instructions,
                'parking_status' => $source->parking_status,
                'customer_type' => $source->customer_type,
                'pet_warning' => $source->pet_warning,
                'charges' => $source->charges,
                'incentive_percentage' => $source->incentive_percentage,
                'special_remarks' => $source->special_remarks,
                'internal_notes' => $source->internal_notes,
                'payment_mode' => $source->payment_mode,
                'payment_status' => $source->payment_status,
                'created_by' => $source->created_by,
                'rescheduled_from_job_id' => $source->id,
            ]);

            // saveQuietly keeps this bookkeeping write from re-triggering the observer.
            $job->forceFill(['rescheduled_at' => now()])->saveQuietly();

            $this->notify($source, $newJob);

            return $newJob;
        });
    }

    /**
     * Remove the auto-scheduled next occurrence when a completed job is walked
     * back to Hold or Pending — but only while that next job is still Pending.
     */
    public function removeNextScheduledJob(Job $job): void
    {
        DB::transaction(function () use ($job): void {
            $nextJobs = Job::query()
                ->where('rescheduled_from_job_id', $job->id)
                ->where('status', JobWorkflowStatus::PENDING->value)
                ->get();

            if ($nextJobs->isEmpty()) {
                return;
            }

            $nextJobs->each(fn (Job $next): ?bool => $next->delete());

            // Clear the marker so completing this job again re-creates the next one.
            $job->forceFill(['rescheduled_at' => null])->saveQuietly();
        });
    }

    private function notify(Job $job, Job $newJob): void
    {
        User::query()->role([CrmRoles::OFFICE_MANAGER])->get()->each(function (User $manager) use ($newJob): void {
            $manager->notify(new JobAutoRescheduledNotification($newJob));
            OptimizationHelper::forgetNotificationUnreadCount($manager->id);
        });

        if ($job->doneByUser) {
            $job->doneByUser->notify(new JobAutoRescheduledNotification($newJob));
            OptimizationHelper::forgetNotificationUnreadCount($job->doneByUser->id);
        }
    }
}
