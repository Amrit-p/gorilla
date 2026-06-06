<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleJobsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::ASSIGN_JOBS);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'job_ids'        => ['required', 'array', 'min:1'],
            'job_ids.*'      => ['integer', 'distinct', 'exists:service_jobs,id'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['nullable', 'date_format:H:i,H:i:s'],
        ];
    }
}
