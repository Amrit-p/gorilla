<?php

namespace App\Http\Requests\Admin\Masters;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChecklistRequest extends FormRequest
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
        $checklist = $this->route('checklist');

        return [
            'name'             => [
                'required',
                'string',
                'max:120',
                Rule::unique('checklists', 'name')->ignore($checklist->id),
            ],
            'points'              => ['required', 'array', 'min:1'],
            'points.*.id'         => ['nullable', 'integer'],
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
