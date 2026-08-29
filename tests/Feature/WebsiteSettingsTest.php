<?php

namespace Tests\Feature;

use App\Helpers\OptimizationHelper;
use App\Models\Setting;
use App\Models\User;
use App\Services\TimezoneSettingsRegistrar;
use App\Support\CrmConstants;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(SettingsSeeder::class);
        $this->admin = User::query()->where('email', CrmConstants::ADMIN_EMAIL)->firstOrFail();
    }

    public function test_login_page_shows_site_name_from_settings(): void
    {
        Setting::query()->updateOrCreate(['key' => 'site_name'], ['value' => 'Green Lawn Co']);
        OptimizationHelper::forgetSettings();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Green Lawn Co', false);
    }

    public function test_super_admin_can_update_site_name_via_settings(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.settings.update'), [
                'site_name' => 'Acme Mowing',
                'site_tagline' => 'Field ops',
                'site_logo_initial' => 'A',
                'company_name' => 'Acme Mowing',
                'default_timezone' => 'UTC',
                'map_provider' => 'mapbox',
                'default_currency' => 'USD',
                'notification_email_enabled' => true,
                'notification_in_app_enabled' => true,
                'mail_use_database' => false,
                'seo_meta_title' => 'Acme Lawn SEO',
                'seo_meta_description' => 'Professional mowing services.',
                'seo_meta_keywords' => 'mowing, lawn',
                'seo_og_title' => '',
                'seo_og_description' => '',
                'seo_robots_noindex' => false,
            ])
            ->assertOk();

        auth()->logout();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Acme Mowing', false)
            ->assertSee('Field ops', false)
            ->assertSee('Professional mowing services.', false);
    }

    public function test_settings_seeder_defaults_timezone_to_auckland(): void
    {
        $this->assertSame(
            'Pacific/Auckland',
            Setting::query()->where('key', 'default_timezone')->value('value')
        );
    }

    public function test_stored_timezone_setting_is_applied_to_the_application(): void
    {
        $original = date_default_timezone_get();

        try {
            Setting::query()->updateOrCreate(['key' => 'default_timezone'], ['value' => 'Pacific/Auckland']);
            OptimizationHelper::forgetSettings();

            TimezoneSettingsRegistrar::applyFromDatabase();

            $this->assertSame('Pacific/Auckland', config('app.timezone'));
            $this->assertSame('Pacific/Auckland', date_default_timezone_get());
            $this->assertSame('Pacific/Auckland', now()->getTimezone()->getName());
        } finally {
            date_default_timezone_set($original);
        }
    }

    public function test_invalid_timezone_setting_falls_back_to_configured_timezone(): void
    {
        $original = date_default_timezone_get();

        try {
            config(['app.timezone' => 'Pacific/Auckland']);
            Setting::query()->updateOrCreate(['key' => 'default_timezone'], ['value' => 'Not/AZone']);
            OptimizationHelper::forgetSettings();

            TimezoneSettingsRegistrar::applyFromDatabase();

            $this->assertSame('Pacific/Auckland', config('app.timezone'));
            $this->assertSame('Pacific/Auckland', date_default_timezone_get());
        } finally {
            date_default_timezone_set($original);
        }
    }

    public function test_super_admin_can_change_the_default_timezone(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.settings.update'), $this->settingsPayload(['default_timezone' => 'Pacific/Auckland']))
            ->assertOk();

        $this->assertSame(
            'Pacific/Auckland',
            Setting::query()->where('key', 'default_timezone')->value('value')
        );
    }

    public function test_default_timezone_must_be_a_valid_identifier(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.settings.update'), $this->settingsPayload(['default_timezone' => 'Middle/Earth']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('default_timezone');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function settingsPayload(array $overrides = []): array
    {
        return array_merge([
            'site_name' => 'Acme Mowing',
            'site_tagline' => 'Field ops',
            'site_logo_initial' => 'A',
            'company_name' => 'Acme Mowing',
            'default_timezone' => 'Pacific/Auckland',
            'map_provider' => 'mapbox',
            'default_currency' => 'USD',
            'notification_email_enabled' => true,
            'notification_in_app_enabled' => true,
            'mail_use_database' => false,
            'seo_meta_title' => '',
            'seo_meta_description' => '',
            'seo_meta_keywords' => '',
            'seo_og_title' => '',
            'seo_og_description' => '',
            'seo_robots_noindex' => false,
        ], $overrides);
    }
}
