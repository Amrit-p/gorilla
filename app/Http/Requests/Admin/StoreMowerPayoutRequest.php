<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMowerPayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_SALARY_CALCULATOR);
    }

    /**
     * The payout modal submits one card per mower. A card either creates a new
     * payout for a set of unpaid jobs, or corrects an existing one.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payouts' => ['required', 'array', 'min:1'],
            'payouts.*.mode' => ['required', Rule::in(['create', 'update'])],
            'payouts.*.payout_id' => ['required_if:payouts.*.mode,update', 'nullable', 'integer', 'exists:mower_payouts,id'],
            'payouts.*.mower_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $mower = User::find($value);

                    if (! $mower || ! $mower->hasRole(CrmRoles::MOWER)) {
                        $fail('The selected user is not a Mower.');
                    }
                },
            ],
            'payouts.*.job_ids' => ['required_if:payouts.*.mode,create', 'array', 'min:1'],
            'payouts.*.job_ids.*' => ['required', 'integer', 'exists:service_jobs,id'],
            'payouts.*.amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'payouts.*.bonus' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'payouts.*.comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payouts.required' => 'Select at least one completed job to pay out.',
            'payouts.*.job_ids.required_if' => 'Select at least one completed job to pay out.',
        ];
    }
}
