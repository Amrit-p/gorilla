<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Metadata for a single job before/after image on the public disk.
 */
final class JobStoredImage
{
    public function __construct(
        public readonly string $id,
        public readonly string $path,
        public readonly string $thumbPath,
        public readonly int $sizeBytes,
        public readonly string $uploadedAt,
    ) {}

    /**
     * @param  mixed  $raw  Legacy string path or stored array record.
     */
    public static function fromMixed(mixed $raw): ?self
    {
        if (is_string($raw) && $raw !== '') {
            $id = pathinfo($raw, PATHINFO_FILENAME);

            return new self(
                id: $id !== '' ? $id : (string) Str::uuid(),
                path: $raw,
                thumbPath: '',
                sizeBytes: 0,
                uploadedAt: now()->toIso8601String(),
            );
        }

        if (! is_array($raw)) {
            return null;
        }

        $path = (string) ($raw['path'] ?? '');
        if ($path === '') {
            return null;
        }

        return new self(
            id: (string) ($raw['id'] ?? pathinfo($path, PATHINFO_FILENAME)),
            path: $path,
            thumbPath: (string) ($raw['thumb_path'] ?? ''),
            sizeBytes: (int) ($raw['size_bytes'] ?? 0),
            uploadedAt: (string) ($raw['uploaded_at'] ?? now()->toIso8601String()),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'thumb_path' => $this->thumbPath,
            'size_bytes' => $this->sizeBytes,
            'uploaded_at' => $this->uploadedAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toPresentation(): array
    {
        $disk = Storage::disk((string) config('job-images.disk', 'public'));
        $url = $disk->url($this->path);
        $thumbUrl = $url;
        if ($this->thumbPath !== '') {
            $thumbUrl = $disk->url($this->thumbPath);
        }

        return [
            'id' => $this->id,
            'path' => $this->path,
            'thumb_path' => $this->thumbPath,
            'url' => $url,
            'thumb_url' => $thumbUrl,
            'size_bytes' => $this->sizeBytes,
            'uploaded_at' => $this->uploadedAt,
        ];
    }
}
