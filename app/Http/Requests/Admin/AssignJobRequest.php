<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;

use Illuminate\Foundation\Http\FormRequest;

class AssignJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::ASSIGN_JOBS);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'job_ids'          => ['required', 'array', 'min:1'],
            'job_ids.*'        => ['integer', 'distinct', 'exists:service_jobs,id'],
            'done_by_user_id'  => ['nullable', 'integer', 'exists:users,id'],
            'employee_ids'     => ['nullable', 'array'],
            'employee_ids.*'   => ['integer', 'exists:users,id'],
        ];
    }
}
