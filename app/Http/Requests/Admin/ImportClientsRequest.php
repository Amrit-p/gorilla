<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class ImportClientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_CUSTOMERS);
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'import_file' => [
                'required',
                'file',
                'mimes:xlsx',
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'max:8192',
            ],
            'create_jobs' => ['nullable', 'boolean'],
        ];
    }
}
