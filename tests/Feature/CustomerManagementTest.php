<?php

namespace Tests\Feature;

use App\Enums\ClientCustomerType;
use App\Enums\ClientPaymentStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadReCompletionDays;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Enums\JobCustomerType;
use App\Enums\JobParkingStatus;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\Lead;
use App\Models\Recurrence;
use App\Models\User;
use App\Support\ServiceTypes;
use Database\Seeders\JobLevelSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->seed(RecurrenceSeeder::class);
        $this->seed(JobLevelSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    public function test_customer_creation_assigns_unique_id(): void
    {
        $payload = $this->validCustomerPayload();

        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $payload)
            ->assertRedirect();

        $client = Client::query()->where('address', $payload['address'])->first();
        $this->assertNotNull($client);
        $this->assertSame(2001, $client->customer_unique_id);
        $this->assertSame(ClientCustomerType::HARD->value, $client->customer_type);
    }

    public function test_lead_conversion_sets_customer_fields(): void
    {
        $lead = Lead::query()->create([
            'client_name' => 'Converted Customer',
            'address' => '88 Convert St',
            'lead_date' => now()->toDateString(),
            'lead_time' => '14:00',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            're_completion_days' => LeadReCompletionDays::DAYS_14->value,
            'job_type' => LeadJobType::REGULAR->value,
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => ClientPaymentStatus::PENDING->value,
            'remarks' => 'Converted remarks',
            'status' => LeadStatus::NEW->value,
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.leads.status.update', $lead), ['status' => LeadStatus::WON->value])
            ->assertOk();

        $client = Client::query()->where('lead_id', $lead->id)->first();
        $this->assertNotNull($client);
        $this->assertNotNull($client->customer_unique_id);
        $this->assertSame(ClientCustomerType::DONT_KNOW->value, $client->customer_type);
        $this->assertSame('Converted remarks', $client->special_remarks);
        $this->assertSame($lead->equipment_type_id, $client->equipment_type_id);
    }

    public function test_customer_advanced_filters_return_matching_rows(): void
    {
        Client::query()->create(array_merge($this->validCustomerPayload(), [
            'address' => 'Easy Filter Ave',
            'customer_type' => ClientCustomerType::EASY->value,
        ]));

        Client::query()->create(array_merge($this->validCustomerPayload(), [
            'address' => 'Hard Filter Blvd',
            'customer_type' => ClientCustomerType::HARD->value,
        ]));

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.clients.index', ['customer_type' => ClientCustomerType::HARD->value]))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $html = (string) $response->json('html');
        $this->assertStringContainsString('Hard Filter Blvd', $html);
        $this->assertStringNotContainsString('Easy Filter Ave', $html);
    }

    public function test_customer_details_page_renders_tabs(): void
    {
        $client = Client::query()->create($this->validCustomerPayload());

        $this->actingAs($this->admin)
            ->get(route('admin.clients.show', $client))
            ->assertOk()
            ->assertSee('Details')
            ->assertSee('Jobs')
            ->assertSee('#'.$client->customer_unique_id, false);
    }

    public function test_jobs_tab_loads_via_ajax_with_statistics(): void
    {
        $client = Client::query()->create($this->validCustomerPayload());

        Job::query()->create([
            'client_id' => $client->id,
            'client_address' => $client->address,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => 'Received',
            'status' => 'Completed',
            'created_by' => $this->admin->id,
        ]);

        Job::query()->create([
            'client_id' => $client->id,
            'client_address' => $client->address,
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '11:00',
            'estimated_duration_minutes' => 45,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => 'Easy',
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => 'Received',
            'status' => 'Pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['client_id' => $client->id]))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertStringContainsString('Completed', $response->json('html'));
    }

    public function test_customer_show_displays_job_statistics(): void
    {
        $client = Client::query()->create($this->validCustomerPayload());

        Job::query()->create([
            'client_id' => $client->id,
            'client_address' => $client->address,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => 'Easy',
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => 'Received',
            'status' => 'Completed',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.clients.show', $client))
            ->assertOk()
            ->assertSee('Total jobs')
            ->assertSee('Completed');
    }

    /**
     * @return array<string, mixed>
     */
    private function validCustomerPayload(): array
    {
        return [
            'name' => 'Gorilla Customer',
            'address' => '100 Customer Way',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            're_completion_days' => LeadReCompletionDays::DAYS_14->value,
            'job_type' => LeadJobType::REGULAR->value,
            'safety_concerns' => ['Pet'],
            'charges' => '99.00',
            'phone' => '555-3000',
            'email' => 'customer@example.com',
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => ClientPaymentStatus::DONE->value,
            'customer_type' => ClientCustomerType::HARD->value,
            'parking_status' => JobParkingStatus::EASY->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            'recurrence_id' => Recurrence::query()->where('is_active', true)->value('id'),
            'additional_site_instructions' => 'Use side gate',
            'pet_warning' => 'Dog in backyard',
            'special_remarks' => 'VIP customer',
        ];
    }
}
