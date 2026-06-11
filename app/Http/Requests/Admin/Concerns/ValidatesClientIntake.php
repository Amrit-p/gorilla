<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\ClientCustomerType;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadWeedSpray;
use App\Models\Recurrence;
use App\Support\EquipmentTypes;
use App\Support\ServiceTypes;
use Illuminate\Validation\Rule;

trait ValidatesClientIntake
{
    /**
     * @return array<string, array<int, mixed>|string>
     */
    protected function clientIntakeRules(): array
    {
        return [
            'address' => ['required', 'string', 'max:255'],
            'equipment_type_id' => EquipmentTypes::idRules(),
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'service_types' => ['required', 'array', 'min:1'],
            'service_types.*' => ServiceTypes::itemRules(),
            'weed_spray' => ['required', Rule::in(LeadWeedSpray::values())],
            'recurrence_id' => ['required', 'integer', Rule::exists(Recurrence::class, 'id')->where('is_active', true)],
            'job_type' => ['required', Rule::in(LeadJobType::values())],
            'charges' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'estimated_time' => ['nullable', 'string', 'max:50'],
            'schedule_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'payment_mode' => ['required', Rule::in(LeadPaymentMode::values())],
            'remarks_type' => ['nullable', 'string', 'max:120'],
            'customer_type' => ['required', Rule::in(ClientCustomerType::values())],
            'additional_site_instructions' => ['nullable', 'string', 'max:5000'],
            'special_remarks' => ['nullable', 'string', 'max:5000'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'accounting_level_id' => ['nullable', 'exists:accounting_levels,id'],
            'job_level_id' => ['nullable', 'exists:job_levels,id'],
            'documents' => ['nullable', 'array', 'max:20'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp', 'max:10240'],
        ];
    }
}
