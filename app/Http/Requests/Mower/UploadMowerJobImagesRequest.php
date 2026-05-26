<?php

namespace App\Http\Requests\Mower;

use App\Http\Requests\Concerns\ValidatesJobImageUpload;
use Illuminate\Foundation\Http\FormRequest;

class UploadMowerJobImagesRequest extends FormRequest
{
    use ValidatesJobImageUpload;
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return $this->jobImageUploadRules();
    }
}
