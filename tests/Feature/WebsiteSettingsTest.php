<?php

namespace Tests\Feature;

use App\Helpers\OptimizationHelper;
use App\Models\Setting;
use App\Models\User;
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
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
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
}
