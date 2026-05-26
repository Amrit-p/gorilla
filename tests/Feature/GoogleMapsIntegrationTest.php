<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadReCompletionDays;
use App\Enums\LeadWeedSpray;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Support\GoogleMapsSettings;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleMapsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    public function test_api_key_resolves_from_settings_over_env(): void
    {
        config(['services.google.maps_key' => 'env-key-test']);

        Setting::query()->updateOrCreate(
            ['key' => 'google_maps_api_key'],
            ['value' => 'settings-key-test']
        );
        \App\Helpers\OptimizationHelper::forgetSettings();

        $this->assertSame('settings-key-test', GoogleMapsSettings::apiKey());
    }

    public function test_lead_create_saves_coordinates_from_form(): void
    {
        $payload = $this->validLeadPayload();
        $payload['latitude'] = '43.7000000';
        $payload['longitude'] = '-79.4000000';

        $this->actingAs($this->admin)
            ->postJson(route('admin.leads.store'), $payload)
            ->assertCreated();

        $lead = Lead::query()->where('address', $payload['address'])->firstOrFail();
        $this->assertEqualsWithDelta(43.7, (float) $lead->latitude, 0.0001);
        $this->assertEqualsWithDelta(-79.4, (float) $lead->longitude, 0.0001);
    }

    public function test_invalid_latitude_is_rejected(): void
    {
        $payload = $this->validLeadPayload();
        $payload['latitude'] = '95';
        $payload['longitude'] = '-79.3832';

        $this->actingAs($this->admin)
            ->postJson(route('admin.leads.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_longitude_requires_latitude_pair(): void
    {
        $payload = $this->validLeadPayload();
        $payload['latitude'] = null;
        $payload['longitude'] = '-79.3832';

        $this->actingAs($this->admin)
            ->postJson(route('admin.leads.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_lead_form_renders_address_picker_with_fallback_without_api_key(): void
    {
        Setting::query()->where('key', 'google_maps_api_key')->delete();
        config(['services.google.maps_key' => null]);
        \App\Helpers\OptimizationHelper::forgetSettings();

        $this->actingAs($this->admin)
            ->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('data-crm-address-picker="lead"', false)
            ->assertSee('Website Settings', false)
            ->assertSee('google-maps-loader.js', false);
    }

    public function test_lead_form_loads_google_scripts_when_api_key_configured(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'google_maps_api_key'],
            ['value' => 'test-google-key']
        );
        \App\Helpers\OptimizationHelper::forgetSettings();

        $this->actingAs($this->admin)
            ->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('google-address-picker.js', false)
            ->assertSee('crmAddressPickerQueue', false);
    }

    public function test_map_jobs_endpoint_returns_geocoded_jobs_with_equipment_color(): void
    {
        $equipment = EquipmentType::query()->where('is_active', true)->firstOrFail();
        $equipment->update(['color_code' => '#ff5500']);

        $lead = Lead::query()->create(array_merge($this->validLeadPayload(), [
            'equipment_type_id' => $equipment->id,
            'latitude' => '43.6532',
            'longitude' => '-79.3832',
        ]));

        $client = Client::query()->create([
            'name' => 'Map Client',
            'address' => $lead->address,
            'phone' => '555-0100',
            'email' => 'mapclient@example.com',
            'lead_id' => $lead->id,
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => 'No',
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'customer_type' => JobCustomerType::EASY->value,
        ]);

        $job = Job::query()->create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'equipment_type_id' => $equipment->id,
            'client_address' => $lead->address,
            'latitude' => '43.6532',
            'longitude' => '-79.3832',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'status' => JobWorkflowStatus::STARTED->value,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.maps.jobs', ['scheduled_date' => now()->toDateString()]))
            ->assertOk();

        $jobs = collect($response->json('jobs'));
        $mapped = $jobs->firstWhere('id', $job->id);

        $this->assertNotNull($mapped);
        $this->assertSame(43.6532, $mapped['lat']);
        $this->assertSame(-79.3832, $mapped['lng']);
        $this->assertSame('#ff5500', $mapped['equipment_color']);
        $this->assertSame($equipment->name, $mapped['equipment_name']);
        $this->assertSame('555-0100', $mapped['client_phone']);
        $this->assertSame('mapclient@example.com', $mapped['client_email']);
        $this->assertSame(route('admin.jobs.show', $job), $mapped['show_url']);
    }

    public function test_map_page_renders_google_config_when_provider_is_google(): void
    {
        Setting::query()->updateOrCreate(['key' => 'map_provider'], ['value' => 'google']);
        Setting::query()->updateOrCreate(['key' => 'google_maps_api_key'], ['value' => 'page-test-key']);
        \App\Helpers\OptimizationHelper::forgetSettings();

        $this->actingAs($this->admin)
            ->get(route('admin.maps.index'))
            ->assertOk()
            ->assertSee('crmJobsMapConfig', false)
            ->assertSee('google-jobs-map.js', false)
            ->assertSee('Google Maps', false);
    }

    public function test_map_page_uses_leaflet_fallback_without_google_key(): void
    {
        Setting::query()->updateOrCreate(['key' => 'map_provider'], ['value' => 'google']);
        Setting::query()->where('key', 'google_maps_api_key')->delete();
        config(['services.google.maps_key' => null]);
        \App\Helpers\OptimizationHelper::forgetSettings();

        $this->actingAs($this->admin)
            ->get(route('admin.maps.index'))
            ->assertOk()
            ->assertSee('leaflet', false)
            ->assertSee('OpenStreetMap fallback', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function validLeadPayload(): array
    {
        return [
            'client_name' => 'Maps Lead',
            'address' => '100 Map Test Avenue',
            'latitude' => '43.6532',
            'longitude' => '-79.3832',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            're_completion_days' => LeadReCompletionDays::DAYS_14->value,
            'job_type' => LeadJobType::REGULAR->value,
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => LeadPaymentStatus::DONE->value,
            'remarks' => 'Map test',
            'lead_date' => now()->toDateString(),
            'lead_time' => '09:00',
            'status' => \App\Enums\LeadStatus::NEW->value,
        ];
    }
}
