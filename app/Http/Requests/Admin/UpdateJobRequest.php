<?php

namespace App\Http\Requests\Admin;

use App\Enums\JobWorkflowStatus;
use App\Http\Requests\Admin\Concerns\ValidatesJobOperational;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobRequest extends FormRequest
{
    use ValidatesJobOperational;

    public function authorize(): bool
    {
        return CrmPermissions::canManageJobRecords($this->user()) || CrmPermissions::canAssignJobs($this->user());
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->jobOperationalRules(), [
            'lead_id' => ['nullable', 'exists:leads,id'],
            'is_recurring' => ['required', 'boolean'],
            'route_sequence' => ['required', 'integer', 'min:0'],
            'priority' => ['required', 'in:Low,Medium,High,Urgent'],
            'status' => ['required', Rule::in(JobWorkflowStatus::values())],
            'internal_notes' => ['nullable', 'string'],
        ]);
    }
}
