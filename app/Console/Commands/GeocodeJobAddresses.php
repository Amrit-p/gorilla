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
            ->with(['lead:id,address,latitude,longitude'])
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
        $errors = [];

        $this->withProgressBar($jobs, function (Job $job) use (&$geocoded, &$errors): void {
            $address = $this->resolveAddress($job);
            $result = $this->geocode($address);

            if ($result['error'] !== null) {
                $errors[] = "Job #{$job->id} ({$address}): {$result['error']}";

                return;
            }

            $job->forceFill([
                'latitude' => $result['lat'],
                'longitude' => $result['lng'],
            ])->save();

            $geocoded++;
        });

        $this->newLine(2);
        $this->info("Geocoded {$geocoded} job(s).");

        if ($errors !== []) {
            $this->warn(count($errors).' job(s) failed:');
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->line("  - {$error}");
            }
            if (count($errors) > 10) {
                $this->line('  ... and '.(count($errors) - 10).' more.');
            }
        }

        return self::SUCCESS;
    }

    private function resolveAddress(Job $job): ?string
    {
        $address = $job->client_address ?: $job->lead?->address;

        return $address !== null && trim($address) !== '' ? $address : null;
    }

    /**
     * @return array{lat: null, lng: null, error: string}|array{lat: float, lng: float, error: null}
     */
    private function geocode(string $address): array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => GoogleMapsSettings::apiKey(),
        ]);

        if ($response->failed()) {
            return ['lat' => null, 'lng' => null, 'error' => 'HTTP request failed ('.$response->status().')'];
        }

        $body = $response->json();
        $status = $body['status'] ?? 'UNKNOWN';

        if ($status !== 'OK') {
            $message = $body['error_message'] ?? $status;

            return ['lat' => null, 'lng' => null, 'error' => $message];
        }

        $location = $body['results'][0]['geometry']['location'] ?? null;

        if (! isset($location['lat'], $location['lng'])) {
            return ['lat' => null, 'lng' => null, 'error' => 'No location in response'];
        }

        return ['lat' => (float) $location['lat'], 'lng' => (float) $location['lng'], 'error' => null];
    }
}
