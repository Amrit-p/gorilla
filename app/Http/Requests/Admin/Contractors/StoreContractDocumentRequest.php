<?php

namespace App\Http\Requests\Admin\Contractors;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreContractDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_CONTRACTORS);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
