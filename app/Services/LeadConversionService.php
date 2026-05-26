<?php

namespace App\Services;

use App\Enums\ClientCustomerType;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadWonNotification;
use App\Helpers\OptimizationHelper;
use App\Support\CrmRoles;
use Illuminate\Support\Facades\DB;

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
            return $existingByLead;
        }

        $duplicate = $this->findDuplicateClient($lead);
        if ($duplicate) {
            if ($duplicate->lead_id === null) {
                $duplicate->lead_id = $lead->id;
                $duplicate->save();
            }

            return $duplicate->fresh();
        }

        $equipmentLabel = $lead->equipmentType?->name;
        $propertyDetails = $lead->property_details;
        if ($equipmentLabel) {
            $propertyDetails = trim(($propertyDetails ? $propertyDetails."\n" : '').'Equipment: '.$equipmentLabel);
        }

        return Client::query()->create([
            'lead_id' => $lead->id,
            'equipment_type_id' => $lead->equipment_type_id,
            'name' => $lead->client_name ?: $lead->address,
            'email' => $lead->email,
            'phone' => $lead->mobile_number,
            'address' => $lead->address,
            'service_types' => $lead->service_types ?? [],
            'weed_spray' => $lead->weed_spray,
            're_completion_days' => $lead->re_completion_days,
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
}
