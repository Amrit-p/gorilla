<?php

namespace App\Http\Requests\Admin\Masters;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountingLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_MASTERS);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('accountingLevel')?->id;

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('accounting_levels', 'name')->ignore($id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
