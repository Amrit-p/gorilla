<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesJobImageUpload;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;

class UploadJobImagesRequest extends FormRequest
{
    use ValidatesJobImageUpload;
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::UPLOAD_JOB_IMAGES)
            || (bool) $this->user()?->can(CrmPermissions::ASSIGN_JOBS);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return $this->jobImageUploadRules();
    }
}
