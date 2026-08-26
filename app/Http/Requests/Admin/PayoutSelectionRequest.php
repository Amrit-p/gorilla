<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class PayoutSelectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_SALARY_CALCULATOR);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_ids' => ['required', 'array', 'min:1'],
            'job_ids.*' => ['required', 'integer'],
        ];
    }
}
