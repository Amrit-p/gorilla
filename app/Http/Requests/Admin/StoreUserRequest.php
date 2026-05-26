<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesUserFields;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    use ValidatesUserFields;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_USERS);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->userFieldRules(requirePassword: true), [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(config('roles.assignable', []))],
        ]);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active') && ! $this->has('status')) {
            $this->merge([
                'status' => (bool) $this->input('is_active') ? 'Active' : 'Inactive',
            ]);
        }
    }
}
