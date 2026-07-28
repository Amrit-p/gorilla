<?php

namespace App\Services;

use App\Enums\ClientCustomerType;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Helpers\OptimizationHelper;
use App\Models\Client;
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
     * Create or link a client for a converted lead inside the current transaction.
     */
    public function convertLeadToClient(Lead $lead, User $actor): Client
    {
        $lead = Lead::query()->whereKey($lead->id)->lockForUpdate()->firstOrFail();

        $existingByLead = Client::query()->where('lead_id', $lead->id)->first();
        if ($existingByLead) {
            $this->ensureJobFromLead($lead, $existingByLead, $actor);

            return $existingByLead;
        }

        $duplicate = $this->findDuplicateClient($lead);
        if ($duplicate) {
            if ($duplicate->lead_id === null) {
                $duplicate->lead_id = $lead->id;
                $duplicate->save();
            }

            $this->ensureJobFromLead($lead, $duplicate, $actor);

            return $duplicate->fresh();
        }

        $equipmentLabel = $lead->equipmentType?->name;
        $propertyDetails = $lead->property_details;
        if ($equipmentLabel) {
            $propertyDetails = trim(($propertyDetails ? $propertyDetails."\n" : '').'Equipment: '.$equipmentLabel);
        }

        $client = Client::query()->create([
            'lead_id' => $lead->id,
            'zone_id' => $lead->zone_id,
            'recurrence_id' => $lead->recurrence_id,
            'equipment_type_id' => $lead->equipment_type_id,
            'name' => $lead->client_name ?: $lead->address,
            'email' => $lead->email,
            'phone' => $lead->mobile_number,
            'address' => $lead->address,
            'service_types' => $lead->service_types ?? [],
            'weed_spray' => $lead->weed_spray,
            'job_type' => $lead->job_type,
            'charges' => $lead->charges,
            'payment_mode' => $lead->payment_mode,
            'payment_status' => $lead->payment_status ?: LeadPaymentStatus::PENDING->value,
            'latitude' => $lead->latitude,
            'longitude' => $lead->longitude,
            'property_details' => $propertyDetails ?: null,
            'notes' => $lead->remarks,
            'special_remarks' => $lead->remarks,
            'customer_type' => ClientCustomerType::DONT_KNOW->value,
            'client_type' => match ($lead->job_type) {
                'On call' => 'On-Call',
                'New' => 'New',
                default => 'Regular',
            },
            'created_by' => $actor->id,
        ]);

        $this->createJobFromLead($lead, $client, $actor);

        return $client;
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

    public function logConversion(User $actor, Lead $lead, Client $client): void
    {
        $this->activityLogService->log(
            $actor,
            'lead.converted_to_client',
            'Lead converted to client.',
            ['lead_id' => $lead->id, 'client_id' => $client->id]
        );
    }

    private function findDuplicateClient(Lead $lead): ?Client
    {
        $email = trim((string) $lead->email);
        $mobile = trim((string) $lead->mobile_number);
        $address = trim((string) $lead->address);

        if ($email !== '') {
            $byEmail = Client::query()
                ->where('email', $email)
                ->where(function ($query) use ($lead): void {
                    $query->whereNull('lead_id')->orWhere('lead_id', '!=', $lead->id);
                })
                ->first();

            if ($byEmail) {
                return $byEmail;
            }
        }

        if ($mobile !== '' && $address !== '') {
            return Client::query()
                ->where('phone', $mobile)
                ->where('address', $address)
                ->where(function ($query) use ($lead): void {
                    $query->whereNull('lead_id')->orWhere('lead_id', '!=', $lead->id);
                })
                ->first();
        }

        return null;
    }

    public static function shouldConvert(string $status): bool
    {
        return LeadStatus::convertsToClientValue($status);
    }

    /**
     * Create a job from the lead when one does not already exist for this lead+client pair.
     */
    public function ensureJobFromLead(Lead $lead, Client $client, User $actor): void
    {
        $exists = Job::query()
            ->where('lead_id', $lead->id)
            ->where('client_id', $client->id)
            ->exists();

        if ($exists) {
            return;
        }

        $this->createJobFromLead($lead, $client, $actor);
    }

    public function createJobFromLead(Lead $lead, Client $client, User $actor): void
    {
        if (empty($lead->service_types)) {
            return;
        }

        $job = Job::query()->create([
            'client_id' => $client->id,
            'recurrence_id' => $lead->recurrence_id,
            'is_recurring' => $lead->recurrence_id !== null,
            'lead_id' => $lead->id,
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
            'estimated_duration_minutes' => EstimatedDurationMinutes::resolve($client->estimated_time),
            'charges' => $lead->charges ?? $client->charges,
            'special_remarks' => $lead->remarks,
            'customer_type' => ClientCustomerType::DONT_KNOW->value,
        ]);

        $this->activityLogService->log(
            $actor,
            'job.created_from_lead',
            'Job created from lead conversion.',
            ['job_id' => $job->id, 'lead_id' => $lead->id, 'client_id' => $client->id]
        );
    }
}
