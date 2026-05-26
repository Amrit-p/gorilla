<?php

namespace App\Http\Requests\Mower;

use App\Enums\JobOperationalPaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMowerJobPaymentRequest extends FormRequest
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
            'payment_status' => ['required', Rule::in(JobOperationalPaymentStatus::values())],
            'payment_pending_reason' => [
                'nullable',
                'required_if:payment_status,'.JobOperationalPaymentStatus::PENDING->value,
                'string',
                'max:255',
            ],
        ];
    }
}
