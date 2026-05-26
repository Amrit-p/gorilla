<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;

use Illuminate\Foundation\Http\FormRequest;

class MapBoundsRequest extends FormRequest
{
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
        return [
            'scheduled_date' => ['nullable', 'date'],
        ];
    }
}
