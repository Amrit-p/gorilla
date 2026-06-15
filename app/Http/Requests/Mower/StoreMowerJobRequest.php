<?php

namespace App\Http\Requests\Mower;

use App\Http\Requests\Admin\Concerns\ValidatesJobOperational;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreMowerJobRequest extends FormRequest
{
    use ValidatesJobOperational;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::UPLOAD_JOB_IMAGES);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return $this->jobOperationalRules();
    }
}
