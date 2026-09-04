<?php

namespace App\Http\Requests\Mower;

use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Models\Job;
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
                Rule::requiredIf(fn (): bool => $this->pendingDetailsRequired()),
                'string',
                'max:255',
            ],
            'payment_pending_amount' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->pendingDetailsRequired()),
                'numeric',
                'min:0',
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
        ];
    }

    /**
     * A pending reason and amount are only needed when the customer owes cash on
     * site — online payments are tracked automatically, so there's nothing to explain.
     */
    private function pendingDetailsRequired(): bool
    {
        if ($this->input('payment_status') !== JobOperationalPaymentStatus::PENDING->value) {
            return false;
        }

        $job = $this->route('job');

        return ! $job instanceof Job || $job->payment_mode !== JobOperationalPaymentMode::ONLINE->value;
    }
}
