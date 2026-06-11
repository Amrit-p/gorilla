<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadWeedSpray;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Support\CrmRoles;
use App\Support\EquipmentTypes;
use App\Support\ServiceTypes;
use Illuminate\Validation\Rule;

trait ValidatesLeadIntake
{
    /**
     * @return array<string, array<int, mixed>|string>
     */
    protected function intakeRules(): array
    {
        return [
            'zone_id' => ['nullable', 'integer', Rule::exists(Zone::class, 'id')->where('is_active', true)],
            'client_name' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'lead_date' => ['nullable', 'date'],
            'service_types' => ['required', 'array', 'min:1'],
            'service_types.*' => ServiceTypes::itemRules(),
            'weed_spray' => ['required', Rule::in(LeadWeedSpray::values())],
            'equipment_type_id' => EquipmentTypes::idRules(),
            'recurrence_id' => ['required', 'integer', Rule::exists(Recurrence::class, 'id')->where('is_active', true)],
            'job_type' => ['required', Rule::in(LeadJobType::values())],
            'charges' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'mobile_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'payment_mode' => ['required', Rule::in(LeadPaymentMode::values())],
            'payment_status' => ['required', Rule::in(LeadPaymentStatus::values())],
            'remarks' => [
                Rule::requiredIf(fn (): bool => $this->input('payment_status') === LeadPaymentStatus::PENDING->value),
                'nullable',
                'string',
                'max:5000',
            ],
            'assigned_sales_user_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value) {
                        return;
                    }
                    $exists = User::query()
                        ->role(CrmRoles::SALES_MANAGER)
                        ->where('is_active', true)
                        ->whereKey((int) $value)
                        ->exists();
                    if (! $exists) {
                        $fail('Leads can only be assigned to active sales managers.');
                    }
                },
            ],
        ];
    }
}
