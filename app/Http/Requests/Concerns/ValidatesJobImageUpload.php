<?php

namespace App\Http\Requests\Concerns;

trait ValidatesJobImageUpload
{
    /**
     * @return array<string, array<int, mixed>|string>
     */
    protected function jobImageUploadRules(): array
    {
        $maxFiles = (int) config('job-images.max_files_per_request', 6);
        $maxKb = (int) config('job-images.max_upload_kb', 10240);

        return [
            'images' => ['required', 'array', 'min:1', 'max:'.$maxFiles],
            'images.*' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:'.$maxKb,
            ],
        ];
    }
}
