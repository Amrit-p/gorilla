<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesUserFields;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')?->id;

        return array_merge($this->userFieldRules(requirePassword: false), [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['required', 'string', Rule::in(array_merge(
                config('roles.assignable', []),
                [config('roles.super_admin')]
            ))],
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
