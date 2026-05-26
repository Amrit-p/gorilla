<?php

namespace App\Services;

use App\Enums\JobImageKind;
use App\Support\JobStoredImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class JobImageStorageService
{
    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, JobStoredImage>
     */
    public function storeJobImages(int $jobId, JobImageKind $kind, array $files): array
    {
        $stored = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $stored[] = $this->storeJobImage($jobId, $kind, $file);
        }

        return $stored;
    }

    public function storeJobImage(int $jobId, JobImageKind $kind, UploadedFile $file): JobStoredImage
    {
        $this->guardAllowedUpload($file);

        $id = (string) Str::uuid();
        $directory = $this->imageDirectory($jobId, $kind);
        $thumbDirectory = $this->thumbDirectory($jobId, $kind);

        $disk = $this->disk();
        $disk->makeDirectory($directory);
        $disk->makeDirectory($thumbDirectory);

        $filename = $id.'.jpg';
        $path = $directory.'/'.$filename;
        $thumbPath = $thumbDirectory.'/'.$filename;

        $fullBinary = $this->encodeJpeg($file->getRealPath(), 'full');
        $thumbBinary = $this->encodeJpeg($file->getRealPath(), 'thumb');

        $disk->put($path, $fullBinary);
        $disk->put($thumbPath, $thumbBinary);

        return new JobStoredImage(
            id: $id,
            path: $path,
            thumbPath: $thumbPath,
            sizeBytes: strlen($fullBinary),
            uploadedAt: now()->toIso8601String(),
        );
    }

    public function deleteStoredImage(JobStoredImage $image): void
    {
        $disk = $this->disk();

        if ($image->path !== '' && $this->isSafeJobImagePath($image->path)) {
            $disk->delete($image->path);
        }

        if ($image->thumbPath !== '' && $this->isSafeJobImagePath($image->thumbPath)) {
            $disk->delete($image->thumbPath);
        }
    }

    public function imageDirectory(int $jobId, JobImageKind $kind): string
    {
        return 'jobs/'.$jobId.'/'.$kind->directorySegment();
    }

    public function thumbDirectory(int $jobId, JobImageKind $kind): string
    {
        return $this->imageDirectory($jobId, $kind).'/thumbs';
    }

    public function guardAllowedUpload(UploadedFile $file): void
    {
        $maxKb = (int) config('job-images.max_upload_kb', 10240);
        if ($file->getSize() > $maxKb * 1024) {
            abort(422, 'Image exceeds maximum upload size.');
        }

        $mime = $this->detectMimeType($file);
        $allowed = config('job-images.allowed_mimes', []);

        if (! in_array($mime, $allowed, true)) {
            abort(422, 'Only JPEG, PNG, WebP, or GIF images are allowed.');
        }

        if (! @getimagesize($file->getRealPath())) {
            abort(422, 'Invalid image file.');
        }
    }

    private function detectMimeType(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw new FileException('Unable to read uploaded file.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected = $finfo ? finfo_file($finfo, $path) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        return is_string($detected) && $detected !== ''
            ? $detected
            : (string) $file->getMimeType();
    }

    private function encodeJpeg(string $sourcePath, string $profile): string
    {
        if (! function_exists('imagecreatefromstring')) {
            $contents = file_get_contents($sourcePath);
            if ($contents === false) {
                abort(422, 'Unable to read uploaded image.');
            }

            return $contents;
        }

        $contents = file_get_contents($sourcePath);
        if ($contents === false) {
            abort(422, 'Unable to read uploaded image.');
        }

        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            abort(422, 'Invalid image file.');
        }

        $config = config('job-images.'.$profile, []);
        $maxWidth = (int) ($config['max_width'] ?? 1920);
        $maxHeight = (int) ($config['max_height'] ?? 1920);
        $quality = (int) ($config['jpeg_quality'] ?? 75);

        $width = imagesx($image);
        $height = imagesy($image);
        [$targetWidth, $targetHeight] = $this->scaledDimensions($width, $height, $maxWidth, $maxHeight);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        ob_start();
        imagejpeg($canvas, null, $quality);
        imagedestroy($canvas);
        $jpeg = ob_get_clean();

        if ($jpeg === false) {
            abort(422, 'Image compression failed.');
        }

        return $jpeg;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function scaledDimensions(int $width, int $height, int $maxWidth, int $maxHeight): array
    {
        $ratio = min($maxWidth / max($width, 1), $maxHeight / max($height, 1), 1);

        return [
            (int) max(1, round($width * $ratio)),
            (int) max(1, round($height * $ratio)),
        ];
    }

    private function isSafeJobImagePath(string $path): bool
    {
        if (str_contains($path, '..')) {
            return false;
        }

        return (bool) preg_match('#^jobs/\d+/(before|after)(/thumbs)?/[a-zA-Z0-9\-]+\.jpg$#', $path)
            || (bool) preg_match('#^job-images/\d+/(before|after)/[a-zA-Z0-9\-]+\.jpg$#', $path);
    }

    private function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk((string) config('job-images.disk', 'public'));
    }
}
