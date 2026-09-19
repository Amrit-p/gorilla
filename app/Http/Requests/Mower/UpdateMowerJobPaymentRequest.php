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
            'payment_status' => [
                'nullable',
                Rule::requiredIf(fn (): bool => ! $this->isOnlinePayment()),
                Rule::in(JobOperationalPaymentStatus::values()),
            ],
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
            'received_amount' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->receivedAmountRequired()),
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

        return ! $this->isOnlinePayment();
    }

    /**
     * The amount collected on site is only recorded when the payment is marked
     * received and isn't an online payment.
     */
    private function receivedAmountRequired(): bool
    {
        if ($this->input('payment_status') !== JobOperationalPaymentStatus::RECEIVED->value) {
            return false;
        }

        return ! $this->isOnlinePayment();
    }

    /**
     * Online payments are tracked automatically, so the mower isn't required to
     * report a payment status — it's only needed for cash/both payment modes.
     */
    private function isOnlinePayment(): bool
    {
        $job = $this->route('job');

        return $job instanceof Job && $job->payment_mode === JobOperationalPaymentMode::ONLINE->value;
    }
}
