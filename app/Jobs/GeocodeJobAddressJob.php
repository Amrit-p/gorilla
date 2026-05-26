<?php

namespace App\Jobs;

use App\Models\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeocodeJobAddressJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(public int $jobId) {}

    public function handle(): void
    {
        $job = Job::query()->find($this->jobId);
        $address = trim((string) ($job?->client_address ?? ''));
        if (! $job || $address === '') {
            return;
        }

        $hash = crc32(strtolower($address));
        $job->latitude = 20 + (($hash % 7000000) / 1000000);
        $job->longitude = 70 + (($hash % 9000000) / 1000000);
        $job->save();
    }
}
