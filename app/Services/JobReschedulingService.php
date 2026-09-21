<?php

namespace App\Services;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        if ($source === null) {
            Log::info('job.reschedule.skipped', ['job_id' => $job->id, 'reason' => 'job not found']);

            return null;
        }

        if ($source->recurrence_id === null) {
            Log::info('job.reschedule.skipped', ['job_id' => $source->id, 'reason' => 'no recurrence_id']);

            return null;
        }

        if ($source->nextOccurrence()->exists()) {
            Log::info('job.reschedule.skipped', [
                'job_id' => $source->id,
                'reason' => 'already rescheduled',
            ]);

            return null;
        }

        $source->loadMissing('recurrence');

        if ($source->recurrence === null) {
            Log::warning('job.reschedule.skipped', [
                'job_id' => $source->id,
                'recurrence_id' => $source->recurrence_id,
                'reason' => 'recurrence record not found',
            ]);

            return null;
        }

        $nextDate = $source->recurrence->resolve($source->scheduled_date->copy());

        if ($nextDate === null) {
            Log::warning('job.reschedule.skipped', [
                'job_id' => $source->id,
                'recurrence_id' => $source->recurrence_id,
                'recurrence_name' => $source->recurrence->name,
                'interval_days' => $source->recurrence->interval_days,
                'reason' => 'recurrence resolve() returned null',
            ]);

            return null;
        }

        return DB::transaction(function () use ($source, $nextDate): Job {
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
                'office_remarks' => $source->office_remarks,
                'internal_notes' => $source->internal_notes,
                'payment_mode' => $source->payment_mode,
                'payment_status' => JobOperationalPaymentStatus::PENDING->value,
                'created_by' => $source->created_by,
                'rescheduled_from_job_id' => $source->id,
            ]);

            Log::info('job.reschedule.created', [
                'source_job_id' => $source->id,
                'new_job_id' => $newJob->id,
                'scheduled_date' => $newJob->scheduled_date->toDateString(),
            ]);

            return $newJob;
        });
    }

    /**
     * Remove the auto-scheduled next occurrence when a completed job is walked
     * back to Hold or Pending.
     *
     * A next occurrence still Pending gets deleted — nobody's touched it yet.
     * One that's progressed further (started/completed) is left alone, since
     * that's real work, not bookkeeping — and its rescheduled_from_job_id link
     * keeps this job's nextOccurrence() from being re-created on re-completion.
     */
    public function removeNextScheduledJob(Job $job): void
    {
        Job::query()
            ->where('rescheduled_from_job_id', $job->id)
            ->where('status', JobWorkflowStatus::PENDING->value)
            ->get()
            ->each(fn (Job $next): ?bool => $next->delete());
    }
}
