<?php

namespace App\Http\Requests\Admin\Masters;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobLevelRequest extends FormRequest
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
        $id = $this->route('jobLevel')?->id;

        return [
            'name'        => ['required', 'string', 'max:120', Rule::unique('job_levels', 'name')->ignore($id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'   => ['required', 'boolean'],
        ];
    }
}
