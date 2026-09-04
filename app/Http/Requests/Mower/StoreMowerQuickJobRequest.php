<?php

namespace App\Http\Requests\Mower;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadWeedSpray;
use App\Models\Recurrence;
use App\Support\CrmPermissions;
use App\Support\EquipmentTypes;
use App\Support\ServiceTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMowerQuickJobRequest extends FormRequest
{
    /**
     * Payment modes that collect money on site, so the amount must be stated.
     *
     * @var array<int, string>
     */
    public const PAYMENT_MODES_REQUIRING_CHARGES = [
        JobOperationalPaymentMode::CASH->value,
        JobOperationalPaymentMode::BOTH->value,
    ];

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
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'service_types' => ['required', 'array', 'min:1'],
            'service_types.*' => ServiceTypes::itemRules(),
            'weed_spray' => ['required', Rule::in(LeadWeedSpray::values())],
            'recurrence_id' => ['required', 'integer', Rule::exists(Recurrence::class, 'id')->where('is_active', true)],
            'equipment_type_id' => EquipmentTypes::mowerIdRules(),
            'customer_type' => ['required', Rule::in(JobCustomerType::values())],
            'payment_mode' => ['required', Rule::in(JobOperationalPaymentMode::values())],
            'charges' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('payment_mode'), self::PAYMENT_MODES_REQUIRING_CHARGES, true)),
                'nullable',
                'numeric',
                'min:0',
            ],
            'scheduled_date' => ['nullable', 'date', 'after_or_equal:today'],
            'schedule_date' => ['nullable', 'date', 'after_or_equal:today'],
            'office_remarks' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::in(JobWorkflowStatus::values())],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'max:5120'],
            'documents' => ['nullable', 'array', 'max:20'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'equipment_type_id' => 'equipment type',
            'office_remarks' => 'comments',
            'status' => 'job status',
            'charges' => 'amount',
        ];
    }
}
