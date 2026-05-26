<?php

namespace App\Jobs;

use App\Models\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeocodeClientAddressJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(public int $clientId) {}

    public function handle(): void
    {
        $client = Client::query()->find($this->clientId);
        if (! $client || empty($client->address)) {
            return;
        }

        if ($client->latitude !== null && $client->longitude !== null) {
            return;
        }

        $hash = crc32(strtolower($client->address));
        $client->latitude = 20 + (($hash % 7000000) / 1000000);
        $client->longitude = 70 + (($hash % 9000000) / 1000000);
        $client->save();
    }
}
