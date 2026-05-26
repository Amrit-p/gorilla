<?php

namespace App\Services;

use App\Enums\JobImageKind;
use App\Models\Job;
use App\Models\User;
use App\Support\JobStoredImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class JobImageManagementService
{
    public function __construct(
        private readonly JobImageStorageService $jobImageStorageService,
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, array<string, mixed>>
     */
    public function upload(Job $job, JobImageKind $kind, array $files, User $actor): array
    {
        $files = array_values(array_filter($files, static fn ($f) => $f instanceof UploadedFile));

        if ($files === []) {
            throw ValidationException::withMessages([
                'images' => ['At least one image is required.'],
            ]);
        }

        $existing = $this->records($job, $kind);
        $maxPerKind = (int) config('job-images.max_per_kind', 50);
        $remaining = $maxPerKind - count($existing);

        if ($remaining <= 0) {
            throw ValidationException::withMessages([
                'images' => ['Maximum number of images reached for this job.'],
            ]);
        }

        if (count($files) > $remaining) {
            throw ValidationException::withMessages([
                'images' => ["You can upload at most {$remaining} more image(s)."],
            ]);
        }

        $stored = $this->jobImageStorageService->storeJobImages($job->id, $kind, $files);
        $merged = array_merge($existing, $stored);

        $job->{$kind->jobAttribute()} = array_map(
            static fn (JobStoredImage $image): array => $image->toArray(),
            $merged
        );
        $job->save();

        $this->activityLogService->log($actor, 'job.'.$kind->value.'_images_uploaded', 'Job images uploaded.', [
            'job_id' => $job->id,
            'kind' => $kind->value,
            'count' => count($stored),
        ]);

        return $this->presentCollection($merged);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function delete(Job $job, JobImageKind $kind, string $imageId, User $actor): array
    {
        $records = $this->records($job, $kind);
        $target = null;
        $remaining = [];

        foreach ($records as $record) {
            if ($record->id === $imageId) {
                $target = $record;

                continue;
            }

            $remaining[] = $record;
        }

        if ($target === null) {
            throw ValidationException::withMessages([
                'image_id' => ['Image not found on this job.'],
            ]);
        }

        $this->jobImageStorageService->deleteStoredImage($target);

        $job->{$kind->jobAttribute()} = array_map(
            static fn (JobStoredImage $image): array => $image->toArray(),
            $remaining
        );
        $job->save();

        $this->activityLogService->log($actor, 'job.'.$kind->value.'_image_deleted', 'Job image deleted.', [
            'job_id' => $job->id,
            'kind' => $kind->value,
            'image_id' => $imageId,
        ]);

        return $this->presentCollection($remaining);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function presentForJob(Job $job, JobImageKind $kind): array
    {
        return $this->presentCollection($this->records($job, $kind));
    }

    /**
     * Legacy admin job attachments (string paths or stored records).
     *
     * @return array<int, array<string, mixed>>
     */
    public function presentAttachedImages(Job $job): array
    {
        $raw = $job->attached_images ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $records = [];
        foreach ($raw as $item) {
            $record = JobStoredImage::fromMixed($item);
            if ($record !== null) {
                $records[] = $record;
            }
        }

        return $this->presentCollection($records);
    }

    /**
     * @return array{before: array<int, array<string, mixed>>, after: array<int, array<string, mixed>>}
     */
    public function presentAllForJob(Job $job): array
    {
        return [
            'before' => $this->presentForJob($job, JobImageKind::BEFORE),
            'after' => $this->presentForJob($job, JobImageKind::AFTER),
        ];
    }

    /**
     * @return array<int, JobStoredImage>
     */
    public function records(Job $job, JobImageKind $kind): array
    {
        $raw = $job->{$kind->jobAttribute()} ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $records = [];
        foreach ($raw as $item) {
            $record = JobStoredImage::fromMixed($item);
            if ($record !== null) {
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * @param  array<int, JobStoredImage>  $records
     * @return array<int, array<string, mixed>>
     */
    private function presentCollection(array $records): array
    {
        return array_map(
            static fn (JobStoredImage $image): array => $image->toPresentation(),
            $records
        );
    }
}
