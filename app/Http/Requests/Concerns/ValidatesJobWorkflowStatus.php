<?php

namespace App\Http\Requests\Concerns;

use App\Enums\JobWorkflowStatus;
use Illuminate\Validation\Rule;

trait ValidatesJobWorkflowStatus
{
    /**
     * @return array<string, array<int, mixed>|string>
     */
    protected function jobWorkflowStatusRules(): array
    {
        return [
            'status' => ['required', Rule::in(JobWorkflowStatus::values())],
        ];
    }
}
