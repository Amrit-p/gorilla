<?php

namespace App\Support;

use App\Services\SettingsService;

/**
 * Resolves Google Maps API key and map provider from Website Settings (with .env fallback).
 */
final class GoogleMapsSettings
{
    public static function apiKey(): ?string
    {
        $settings = app(SettingsService::class)->allAsArray();
        $fromSettings = trim((string) ($settings['google_maps_api_key'] ?? ''));

        if ($fromSettings !== '') {
            return $fromSettings;
        }

        $fromEnv = trim((string) config('services.google.maps_key', ''));

        return $fromEnv !== '' ? $fromEnv : null;
    }

    public static function hasApiKey(): bool
    {
        return self::apiKey() !== null;
    }

    public static function mapProvider(): string
    {
        $provider = (string) (app(SettingsService::class)->allAsArray()['map_provider'] ?? 'mapbox');

        return in_array($provider, ['google', 'mapbox'], true) ? $provider : 'mapbox';
    }

    /** Address pickers and Places autocomplete. */
    public static function isPlacesEnabled(): bool
    {
        return self::hasApiKey();
    }

    /** Admin jobs routing map uses Google when provider + key are configured. */
    public static function useGoogleForRoutingMap(): bool
    {
        return self::mapProvider() === 'google' && self::hasApiKey();
    }

    /**
     * @return array{lat: float, lng: float}
     */
    public static function defaultCenter(): array
    {
        return config('google-maps.default_center', ['lat' => 43.6532, 'lng' => -79.3832]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function loaderConfig(): array
    {
        return [
            'enabled' => self::hasApiKey(),
            'apiKey' => self::apiKey(),
            'libraries' => config('google-maps.libraries', ['places']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function mapPageConfig(): array
    {
        $center = self::defaultCenter();

        return [
            'provider' => self::useGoogleForRoutingMap() ? 'google' : 'leaflet',
            'google' => self::loaderConfig(),
            'defaultCenter' => $center,
            'defaultZoom' => (int) config('google-maps.default_zoom', 10),
            'jobsUrl' => route('admin.maps.jobs'),
            'optimizeUrl' => route('admin.maps.optimize'),
            'assignNearestUrl' => route('admin.maps.assign-nearest'),
            'csrfToken' => csrf_token(),
        ];
    }
}
