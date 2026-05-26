<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Default website and system settings (key-value store).
     */
    public function run(): void
    {
        $defaults = config('mowing.website_defaults', []);

        $defaults = array_merge($defaults, [
            'map_provider' => 'mapbox',
            'default_currency' => 'USD',
            'default_timezone' => 'UTC',
            'notification_email_enabled' => '1',
            'notification_in_app_enabled' => '1',
            'mail_use_database' => '0',
            'mail_mailer' => 'smtp',
            'mail_port' => '587',
            'mail_encryption' => 'tls',
            'mail_from_name' => $defaults['site_name'] ?? config('app.name', 'Mowing CRM'),
        ]);

        foreach ($defaults as $key => $value) {
            Setting::query()->firstOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }
    }
}
