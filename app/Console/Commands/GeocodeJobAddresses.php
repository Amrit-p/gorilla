<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Support\GoogleMapsSettings;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('app:geocode-jobs {--force : Re-geocode jobs that already have coordinates}')]
#[Description('Geocode job addresses via the Google Maps Geocoding API and store latitude/longitude.')]
class GeocodeJobAddresses extends Command
{
    public function handle(): int
    {
        if (! GoogleMapsSettings::hasApiKey()) {
            $this->error('No Google Maps API key configured (Website Settings or GOOGLE_MAPS_API_KEY).');

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');

        $jobs = Job::query()
            ->with(['client:id,address,latitude,longitude', 'lead:id,address,latitude,longitude'])
            ->when(! $force, fn ($query) => $query->where(function ($query): void {
                $query->whereNull('latitude')->orWhereNull('longitude');
            }))
            ->get()
            ->filter(fn (Job $job): bool => $this->resolveAddress($job) !== null);

        if ($jobs->isEmpty()) {
            $this->info('No jobs need geocoding.');

            return self::SUCCESS;
        }

        $geocoded = 0;
        $failed = 0;

        $this->withProgressBar($jobs, function (Job $job) use (&$geocoded, &$failed): void {
            $coordinates = $this->geocode($this->resolveAddress($job));

            if ($coordinates === null) {
                $failed++;

                return;
            }

            $job->forceFill([
                'latitude' => $coordinates['lat'],
                'longitude' => $coordinates['lng'],
            ])->save();

            $geocoded++;
        });

        $this->newLine(2);
        $this->info("Geocoded {$geocoded} job(s).");

        if ($failed > 0) {
            $this->warn("Failed to geocode {$failed} job(s).");
        }

        return self::SUCCESS;
    }

    private function resolveAddress(Job $job): ?string
    {
        $address = $job->client_address ?: $job->client?->address ?: $job->lead?->address;

        return $address !== null && trim($address) !== '' ? $address : null;
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function geocode(string $address): ?array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => GoogleMapsSettings::apiKey(),
        ]);

        if ($response->failed()) {
            return null;
        }

        $body = $response->json();

        if (($body['status'] ?? null) !== 'OK') {
            return null;
        }

        $location = $body['results'][0]['geometry']['location'] ?? null;

        if (! isset($location['lat'], $location['lng'])) {
            return null;
        }

        return ['lat' => (float) $location['lat'], 'lng' => (float) $location['lng']];
    }
}
