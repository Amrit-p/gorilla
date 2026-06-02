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
            'scheduled_date'         => ['nullable', 'date'],
            'date_range'             => ['nullable', 'array'],
            'date_range.start'       => ['nullable', 'date'],
            'date_range.end'         => ['nullable', 'date'],
            'zone_id'          => ['nullable', 'integer'],
            'status'           => ['nullable', 'string',],
            'assignment'       => ['nullable', 'in:assigned,unassigned'],
            'search'           => ['nullable', 'string',],
            'recurrence_id'    => ['nullable', 'integer'],
            'payment_mode'     => ['nullable', 'string',],
            'payment_status'   => ['nullable', 'string',],
            'equipment_type_id'     => ['nullable', 'integer'],
            'customer_type'     => ['nullable', 'string',],
            'service_type'      => ['nullable', 'string',],
            'list_scope'       => ['nullable', 'string',],
        ];
    }
}
