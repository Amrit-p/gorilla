<?php

namespace App\Http\Requests\Employee;

use App\Http\Requests\Concerns\ValidatesJobWorkflowStatus;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeJobStatusRequest extends FormRequest
{
    use ValidatesJobWorkflowStatus;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return CrmPermissions::canViewJobs($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return $this->jobWorkflowStatusRules();
    }
}
