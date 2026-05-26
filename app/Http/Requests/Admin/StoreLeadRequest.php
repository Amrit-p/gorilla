<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;

use App\Http\Requests\Admin\Concerns\ValidatesLeadIntake;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
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
        return $this->intakeRules();
    }
}
