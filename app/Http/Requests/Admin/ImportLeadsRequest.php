<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;

use Illuminate\Foundation\Http\FormRequest;

class ImportLeadsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_LEADS);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'mimetypes:text/plain,text/csv,application/csv',
                'max:4096',
            ],
        ];
    }
}
