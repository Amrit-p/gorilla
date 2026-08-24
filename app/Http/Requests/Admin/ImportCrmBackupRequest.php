<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmRoles;
use Illuminate\Foundation\Http\FormRequest;

class ImportCrmBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole(CrmRoles::OFFICE_MANAGER);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'backup_file' => ['required', 'file', 'extensions:json', 'max:51200'],
            'mower_email_domain' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9.-]+\.[a-z]{2,}$/i'],
            'dry_run' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'backup_file.extensions' => 'The backup must be a .json file.',
            'mower_email_domain.regex' => 'Enter a bare domain such as gorillamowing.co.nz.',
        ];
    }
}
