<?php

namespace Tests\Feature;

use App\Enums\ClientCustomerType;
use App\Enums\ClientPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobCustomerType;
use App\Enums\JobWorkflowStatus;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadReCompletionDays;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Models\Client;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Enums\LeadPaymentStatus;
use App\Models\EquipmentType;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmModuleTest extends TestCase
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

    public function test_lead_create_page_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('Create Lead');
    }

    public function test_lead_can_be_stored_and_redirects(): void
    {
        $payload = $this->validLeadPayload();

        $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $payload)
            ->assertRedirect(route('admin.leads.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'address' => $payload['address'],
        ]);
        $lead = Lead::query()->where('address', $payload['address'])->first();
        $this->assertIsArray($lead?->service_types);
        $this->assertContains($payload['service_types'][0], $lead?->service_types ?? []);
    }

    public function test_lead_edit_page_is_accessible(): void
    {
        $lead = Lead::query()->create($this->validLeadPayload());

        $this->actingAs($this->admin)
            ->get(route('admin.leads.edit', $lead))
            ->assertOk()
            ->assertSee('Edit Lead');
    }

    public function test_lead_import_sample_csv_can_be_downloaded(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.import.sample'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertDownload('leads-import-sample.csv');

        $content = $response->streamedContent();
        $this->assertStringContainsString('client_name,email,mobile_number,address,service_types', $content);
        $this->assertStringContainsString('Sample Property', $content);
    }

    public function test_lead_create_route_is_not_captured_by_show_route(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/leads/create')
            ->assertOk()
            ->assertDontSee('No query results for model');
    }

    public function test_client_create_page_and_store(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.clients.create'))
            ->assertOk()
            ->assertSee('Create Customer');

        $payload = $this->validClientPayload();

        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas('success');

        $client = Client::query()->where('address', $payload['address'])->first();
        $this->assertNotNull($client?->customer_unique_id);
        $this->assertGreaterThanOrEqual(2001, $client->customer_unique_id);
    }

    public function test_client_create_route_is_not_captured_by_show_route(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/clients/create')
            ->assertOk();
    }

    public function test_job_create_page_and_store(): void
    {
        $client = Client::query()->create($this->validClientPayload());

        $this->actingAs($this->admin)
            ->get(route('admin.jobs.create'))
            ->assertOk()
            ->assertSee('Create Job');

        $payload = $this->validJobPayload($client->id);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.jobs.store'), $payload)
            ->assertSessionHas('success');

        $job = Job::query()->where('client_id', $client->id)->firstOrFail();
        $response->assertRedirect(route('admin.jobs.show', $job));

        $this->assertDatabaseHas('service_jobs', [
            'client_id' => $client->id,
            'client_address' => $payload['client_address'],
        ]);
    }

    public function test_job_create_route_is_not_captured_by_show_route(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/jobs/create')
            ->assertOk();
    }

    public function test_user_create_page_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Create User');
    }

    public function test_validation_assets_are_referenced_in_layout(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('jquery.validate.min.js', false)
            ->assertSee('crm-form-validation.js', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function validLeadPayload(): array
    {
        return [
            'client_name' => '123 Test Street',
            'address' => '123 Test Street',
            'latitude' => '43.6532000',
            'longitude' => '-79.3832000',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            're_completion_days' => LeadReCompletionDays::DAYS_14->value,
            'job_type' => LeadJobType::REGULAR->value,
            'charges' => '50.00',
            'mobile_number' => '555-0100',
            'email' => 'lead@example.com',
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => LeadPaymentStatus::DONE->value,
            'remarks' => 'Test lead',
            'lead_date' => now()->toDateString(),
            'lead_time' => '10:30',
            'status' => LeadStatus::NEW->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validClientPayload(): array
    {
        return [
            'name' => '456 Client Ave',
            'address' => '456 Client Ave',
            'service_types' => [collect(ServiceTypes::all())->first(fn ($s) => str_contains($s, 'Cut')) ?? ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::YES->value,
            're_completion_days' => LeadReCompletionDays::DAYS_21->value,
            'job_type' => LeadJobType::REGULAR->value,
            'safety_concerns' => ['Pet'],
            'charges' => '75.00',
            'phone' => '555-0200',
            'email' => 'client@example.com',
            'payment_mode' => LeadPaymentMode::ONLINE->value,
            'payment_status' => ClientPaymentStatus::DONE->value,
            'customer_type' => ClientCustomerType::EASY->value,
            'parking_status' => JobParkingStatus::EASY->value,
            'equipment_type_id' => \App\Models\EquipmentType::query()->where('is_active', true)->value('id'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validJobPayload(int $clientId): array
    {
        return [
            'client_id' => $clientId,
            'client_address' => '456 Client Ave',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'equipment_type_id' => \App\Models\EquipmentType::query()->where('is_active', true)->value('id'),
        ];
    }
}
