<?php

namespace App\Http\Requests\Mower;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobParkingStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadWeedSpray;
use App\Models\Recurrence;
use App\Support\CrmPermissions;
use App\Support\EquipmentTypes;
use App\Support\ServiceTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMowerQuickJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::UPLOAD_JOB_IMAGES);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'service_types' => ['required', 'array', 'min:1'],
            'service_types.*' => ServiceTypes::itemRules(),
            'weed_spray' => ['required', Rule::in(LeadWeedSpray::values())],
            'job_type' => ['required', Rule::in(LeadJobType::values())],
            'recurrence_id' => ['required', 'integer', Rule::exists(Recurrence::class, 'id')->where('is_active', true)],
            'equipment_type_id' => EquipmentTypes::idRules(),
            'customer_type' => ['required', Rule::in(JobCustomerType::values())],
            'payment_mode' => ['required', Rule::in(JobOperationalPaymentMode::values())],
            'parking_status' => ['nullable', Rule::in(JobParkingStatus::values())],
            'charges' => ['nullable', 'numeric', 'min:0'],
            'scheduled_date' => ['nullable', 'date', 'after_or_equal:today'],
            'schedule_date' => ['nullable', 'date', 'after_or_equal:today'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'additional_site_instructions' => ['nullable', 'string', 'max:5000'],
            'site_instructions' => ['nullable', 'string', 'max:5000'],
            'special_remarks' => ['nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'max:5120'],
            'documents' => ['nullable', 'array', 'max:20'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp', 'max:10240'],
        ];
    }
}
