<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesJobOperational;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    use ValidatesJobOperational;

    public function authorize(): bool
    {
        return CrmPermissions::canCreateJobs($this->user());
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return $this->jobOperationalRules();
    }
}
