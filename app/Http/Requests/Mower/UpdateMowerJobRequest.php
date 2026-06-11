<?php

namespace App\Http\Requests\Mower;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMowerJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(JobWorkflowStatus::values())],
            'payment_status' => ['required', Rule::in(JobOperationalPaymentStatus::values())],
            'payment_pending_reason' => [
                'nullable',
                'required_if:payment_status,'.JobOperationalPaymentStatus::PENDING->value,
                'string',
                'max:255',
            ],
            'first_payment' => [
                'nullable',
                'required_if:payment_status,'.JobOperationalPaymentStatus::PARTIAL->value,
                'numeric',
                'min:0',
            ],
            'second_payment' => [
                'nullable',
                'required_if:payment_status,'.JobOperationalPaymentStatus::PARTIAL->value,
                'string',
                'max:255',
            ],
            'consumed_time_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
