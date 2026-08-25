<?php

namespace App\Http\Requests\Admin\Followups;

use App\Enums\FollowupStatus;
use App\Services\FollowupService;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFollowupRequest extends FormRequest
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
            'followable_type' => ['nullable', 'string', Rule::in(FollowupService::ALLOWED_FOLLOWABLE_TYPES)],
            'followable_id' => ['nullable', 'required_with:followable_type', 'integer', 'min:1'],
            'title' => ['required_without:followable_type', 'nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'notes' => ['required', 'string', 'max:5000'],
            'outcome' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(FollowupStatus::values())],
            'next_followup_at' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required_without' => 'A title is required for a general follow-up.',
            'followable_id.required_with' => 'Please pick the record this follow-up belongs to.',
        ];
    }
}
