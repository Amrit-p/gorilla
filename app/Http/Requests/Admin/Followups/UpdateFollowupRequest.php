<?php

namespace App\Http\Requests\Admin\Followups;

use App\Enums\FollowupStatus;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFollowupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_FOLLOWUPS);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'outcome' => ['required', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(FollowupStatus::values())],
            'next_followup_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
