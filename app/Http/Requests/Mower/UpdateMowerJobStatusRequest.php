<?php

namespace App\Http\Requests\Mower;

use App\Http\Requests\Concerns\ValidatesJobWorkflowStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMowerJobStatusRequest extends FormRequest
{
    use ValidatesJobWorkflowStatus;
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return $this->jobWorkflowStatusRules();
    }
}
