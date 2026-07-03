<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class FilterActivityLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_USERS);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_range' => ['nullable', 'array'],
            'date_range.start' => ['nullable', 'date', 'date_format:Y-m-d'],
            'date_range.end' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:date_range.start'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toFilters(): array
    {
        $data = $this->validated();

        return [
            'search' => $data['search'] ?? '',
            'action' => $data['action'] ?? '',
            'user_id' => $data['user_id'] ?? '',
            'date_from' => $data['date_range']['start'] ?? '',
            'date_to' => $data['date_range']['end'] ?? '',
        ];
    }
}
