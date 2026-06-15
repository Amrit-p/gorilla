<?php

namespace App\Http\Requests\Mower;

use App\Http\Requests\Admin\Concerns\ValidatesClientIntake;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreMowerClientRequest extends FormRequest
{
    use ValidatesClientIntake;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::UPLOAD_JOB_IMAGES);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->clientIntakeRules();
        $rules['documents'] = ['required', 'array', 'min:1', 'max:20'];

        return $rules;
    }
}
