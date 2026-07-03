<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\DTOS\Request\Reports\ChecklistReportRequestDTO;
use App\Support\CrmRoles;
use Illuminate\Foundation\Http\FormRequest;

class ChecklistReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date_range' => ['nullable', 'array'],
            'date_range.start' => ['nullable', 'date', 'date_format:Y-m-d'],
            'date_range.end' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:date_range.start'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'checklist_id' => ['nullable', 'integer', 'exists:checklists,id'],
        ];
    }

    public function toDTO(): ChecklistReportRequestDTO
    {
        $data = $this->validated();
        $data['start_date'] = $this->date('date_range.start')?->toDateString();
        $data['end_date'] = $this->date('date_range.end')?->toDateString();

        $data['user_id'] = $this->user()->hasRole(CrmRoles::MOWER)
            ? (string) $this->user()->id
            : (isset($data['user_id']) ? (string) $data['user_id'] : '');

        return ChecklistReportRequestDTO::fromArray($data);
    }
}
