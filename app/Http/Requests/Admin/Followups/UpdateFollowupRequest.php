<?php

namespace App\Http\Requests\Admin\Followups;

use App\Enums\FollowupStatus;
use App\Models\Followup;
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
        $followup = $this->route('followup');
        $isGeneral = $followup instanceof Followup && $followup->isGeneral();

        return [
            'title' => [$isGeneral ? 'required' : 'nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'notes' => ['required', 'string', 'max:5000'],
            'outcome' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(FollowupStatus::values())],
            'next_followup_at' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
