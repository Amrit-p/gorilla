<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\Support\CrmRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class MowerRequestReport extends FormRequest
{
    public function rules(): array
    {
        return [
            'search'            => [
                'nullable',
                'string'
            ],
            'list_scope'        => [
                'nullable',
                'string',
                'in:today,upcoming,done,hold'
            ],
            'status'            => [
                'nullable',
                'string',
                'in:started,completed,hold,cancelled'
            ],
            'priority'          => [
                'nullable',
                'string',
                'in:low,medium,high'
            ],
            'sortBy'            => [
                'nullable',
                'string',
                'in:name,total_jobs_completed,total_jobs_pending,total_cash_earned,total_online_earned,total_sales,incentive_percentage'
            ],
            'sortDirection'     => [
                'nullable',
                'string',
                'in:asc,desc',
            ],
            'date_range'        => [
                'nullable',
                'array'
            ],
            'date_range.start'  => [
                'nullable',
                'date',
                'date_format:Y-m-d'
            ],

            'date_range.end'    => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:date_range.start',
            ],
            'equipment_type_id' => [
                'nullable',
                'integer',
            ],
            'customer_type' => [
                'nullable',
                'string',
            ],
            'service_type' => [
                'nullable',
                'string',
            ],
            'zone_id' => [
                'nullable',
            ],
            'recurrence_id' => [
                'nullable',
            ],
            'client_id' => [
                'nullable',
            ],
            'assignment' => [
                'nullable',
                'string',
                'in:assigned,unassigned',
            ],
            'payment_mode' => [
                'nullable',
                'string',
            ],
            'payment_status' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Normalize incoming string fields to lowercase using multibyte-safe function
     * before validation/comparison. Frontend may send uppercase values.
     */
    protected function prepareForValidation(): void
    {
        $excludeFields = ['service_type'];
        $data = [];

        foreach (array_diff(array_keys($this->rules()), $excludeFields) as $field) {
            if ($this->has($field) && is_string($value = $this->input($field))) {
                $data[$field] = mb_strtolower($value, 'UTF-8');
            }
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function toDTO(): MowerRequestReportDTO
    {
        $data = $this->validated();

        $userId = $this->user()->hasRole(CrmRoles::MOWER)
            ? $this->user()->id
            : '';

        $data['start_date'] = $this->date('date_range.start')?->toDateString();
        $data['end_date']   = $this->date('date_range.end')?->toDateString();
        $data['user_id']    = (string) $userId;

        $dto = MowerRequestReportDTO::fromArray($data);

        return $dto;
    }
}
