<?php

namespace App\Services;

use App\Support\WebsiteSettings;

/**
 * Applies the `default_timezone` key from the settings table onto the application
 * timezone so dates render and persist in the business's local time.
 * Falls back to config/.env when the stored value is missing or not a valid identifier.
 */
class TimezoneSettingsRegistrar
{
    /**
     * Call during application bootstrap (after config is loaded).
     */
    public static function applyFromDatabase(): void
    {
        $fallback = (string) config('app.timezone', 'UTC');
        $timezone = WebsiteSettings::get('default_timezone', $fallback);

        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = $fallback;
        }

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }
}
