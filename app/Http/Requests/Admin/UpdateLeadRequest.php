<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;

use App\Enums\LeadStatus;
use App\Http\Requests\Admin\Concerns\ValidatesLeadIntake;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    use ValidatesLeadIntake;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_LEADS);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->intakeRules(), [
            'assigned_sales_user_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::in(LeadStatus::selectableValues())],
        ]);
    }
}
