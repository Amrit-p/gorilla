<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use Illuminate\Foundation\Http\FormRequest;

class SalaryMonthJobsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_SALARY_CALCULATOR);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mower_id' => [
                'required',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $mower = User::find($value);

                    if (! $mower || ! $mower->hasRole(CrmRoles::MOWER)) {
                        $fail('The selected user is not a Mower.');
                    }
                },
            ],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
}
