<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;

use App\Http\Requests\Admin\Concerns\ValidatesClientIntake;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    use ValidatesClientIntake;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_CUSTOMERS);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->clientIntakeRules(), [
            'lead_id' => ['nullable', 'exists:leads,id'],
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $concerns = $this->input('safety_concerns', []);
            if (in_array('Any Other', $concerns, true) && blank($this->input('safety_other'))) {
                $validator->errors()->add('safety_other', 'Please specify the other safety concern.');
            }
        });
    }
}
