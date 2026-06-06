<?php

namespace App\Http\Requests\Admin\Masters;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return CrmPermissions::canManageMasters($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:120', Rule::unique('checklists', 'name')],
            'points'              => ['required', 'array', 'min:1'],
            'points.*.heading'    => ['required', 'string', 'max:255'],
            'points.*.text'       => ['required', 'string'],
            'points.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'points.required'        => 'At least one point is required.',
            'points.min'             => 'At least one point is required.',
            'points.*.heading.required' => 'Each point must have a heading.',
            'points.*.text.required'   => 'Each point must have text.',
        ];
    }
}
