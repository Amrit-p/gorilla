<?php

namespace App\Services;

use App\Helpers\OptimizationHelper;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * Get settings as key => value array (cached — whole table is a small KV map).
     */
    public function allAsArray(): array
    {
        $ttlSeconds = (int) config('mowing.cache.ttl.settings_seconds', 600);

        return Cache::remember(
            config('mowing.cache_keys.settings_flat'),
            $ttlSeconds,
            fn (): array => Setting::query()->pluck('value', 'key')->toArray()
        );
    }

    /**
     * Persist settings in key-value table.
     */
    public function update(User $actor, array $settings): void
    {
        // Do not overwrite a stored SMTP password when the admin leaves the field blank (rotate via re-entry only).
        if (array_key_exists('mail_password', $settings) && $settings['mail_password'] === '') {
            unset($settings['mail_password']);
        }

        if (array_key_exists('google_maps_api_key', $settings) && $settings['google_maps_api_key'] === '') {
            Setting::query()->where('key', 'google_maps_api_key')->delete();
            unset($settings['google_maps_api_key']);
        }

        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
            );
        }

        OptimizationHelper::forgetSettings();

        $this->activityLogService->log($actor, 'settings.updated', 'System settings updated.', [
            'keys' => array_keys($settings),
        ]);
    }

    public function uploadLogo(User $actor, UploadedFile $file): void
    {
        $previous = Setting::query()->where('key', 'site_logo_path')->value('value');
        if ($previous && Storage::disk('public')->exists($previous)) {
            Storage::disk('public')->delete($previous);
        }

        $path = $file->store('branding', 'public');

        Setting::query()->updateOrCreate(
            ['key' => 'site_logo_path'],
            ['value' => $path]
        );

        OptimizationHelper::forgetSettings();
    }

    public function removeLogo(User $actor): void
    {
        $previous = Setting::query()->where('key', 'site_logo_path')->value('value');
        if ($previous && Storage::disk('public')->exists($previous)) {
            Storage::disk('public')->delete($previous);
        }

        Setting::query()->where('key', 'site_logo_path')->delete();
        OptimizationHelper::forgetSettings();

        $this->activityLogService->log($actor, 'settings.logo_removed', 'Website logo removed.');
    }
}
