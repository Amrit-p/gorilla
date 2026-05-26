<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OptimizeRouteRequest extends FormRequest
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
            'job_ids' => ['required', 'array', 'min:2'],
            'job_ids.*' => ['integer', Rule::exists('service_jobs', 'id')->whereNull('deleted_at')],
        ];
    }
}
