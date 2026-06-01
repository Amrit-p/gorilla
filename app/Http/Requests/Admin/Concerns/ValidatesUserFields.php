<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\UserEfficiency;
use App\Enums\UserStatus;
use Illuminate\Validation\Rule;

trait ValidatesUserFields
{
    /**
     * @return array<string, array<int, mixed>|string>
     */
    protected function userFieldRules(bool $requirePassword = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'efficiency' => ['required', Rule::in(UserEfficiency::values())],
            'incentive_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'status' => ['required', Rule::in(UserStatus::values())],
            'role' => ['required', 'string'],
        ];

        if ($requirePassword) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed', 'required_with:password'];
        }

        return $rules;
    }
}
