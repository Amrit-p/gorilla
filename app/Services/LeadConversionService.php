<?php

namespace App\Services;

use App\Enums\JobCustomerType;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadStatus;
use App\Helpers\OptimizationHelper;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadWonNotification;
use App\Support\CrmRoles;
use App\Support\EstimatedDurationMinutes;

class LeadConversionService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * Convert a won/mature lead into a Hold job with full contact snapshot.
     */
    public function convertLeadToJob(Lead $lead, User $actor): ?Job
    {
        $lead = Lead::query()->whereKey($lead->id)->lockForUpdate()->firstOrFail();

        $existing = Job::query()->where('lead_id', $lead->id)->first();
        if ($existing) {
            return $existing;
        }

        return $this->createJobFromLead($lead, $actor);
    }

    public function notifyManagers(Lead $lead): void
    {
        User::query()
            ->role([CrmRoles::OFFICE_MANAGER])
            ->get()
            ->each(function (User $user) use ($lead): void {
                $user->notify(new LeadWonNotification($lead));
                OptimizationHelper::forgetNotificationUnreadCount($user->id);
            });
    }

    public function logConversion(User $actor, Lead $lead, ?Job $job): void
    {
        $this->activityLogService->log(
            $actor,
            'lead.converted_to_job',
            'Lead converted to job.',
            ['lead_id' => $lead->id, 'job_id' => $job?->id]
        );
    }

    public static function shouldConvert(string $status): bool
    {
        return LeadStatus::convertsToJobValue($status);
    }

    public function createJobFromLead(Lead $lead, User $actor): ?Job
    {
        if (empty($lead->service_types)) {
            return null;
        }

        $job = Job::query()->create([
            'lead_id' => $lead->id,
            'recurrence_id' => $lead->recurrence_id,
            'is_recurring' => $lead->recurrence_id !== null,
            'zone_id' => $lead->zone_id,
            'equipment_type_id' => $lead->equipment_type_id,
            'customer_name' => $lead->client_name ?: $lead->address,
            'email' => $lead->email,
            'phone' => $lead->mobile_number,
            'weed_spray' => $lead->weed_spray,
            'job_type' => $lead->job_type,
            'property_details' => $lead->property_details,
            'notes' => $lead->remarks,
            'client_address' => $lead->address,
            'latitude' => $lead->latitude,
            'longitude' => $lead->longitude,
            'required_services' => $lead->service_types,
            'scheduled_date' => $lead->lead_date ?? now()->toDateString(),
            'scheduled_time' => $lead->lead_time,
            'payment_mode' => $lead->payment_mode,
            'payment_status' => $lead->payment_status,
            'status' => JobWorkflowStatus::HOLD->value,
            'created_by' => $actor->id,
            'estimated_duration_minutes' => EstimatedDurationMinutes::resolve(null),
            'charges' => $lead->charges,
            'special_remarks' => $lead->remarks,
            'customer_type' => JobCustomerType::DONT_KNOW->value,
        ]);

        $this->activityLogService->log(
            $actor,
            'job.created_from_lead',
            'Job created from lead conversion.',
            ['job_id' => $job->id, 'lead_id' => $lead->id]
        );

        return $job;
    }
}
