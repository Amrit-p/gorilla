<?php

namespace Tests\Feature;

use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadReCompletionDays;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Lead;
use App\Models\User;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadManagementTest extends TestCase
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

    public function test_lead_can_be_created_via_ajax_with_map_coordinates(): void
    {
        $payload = $this->validLeadPayload();

        $this->actingAs($this->admin)
            ->postJson(route('admin.leads.store'), $payload)
            ->assertCreated()
            ->assertJsonPath('lead.address', $payload['address']);

        $lead = Lead::query()->where('address', $payload['address'])->first();
        $this->assertNotNull($lead);
        $this->assertSame($payload['client_name'], $lead->client_name);
        $this->assertEqualsWithDelta(43.6532, (float) $lead->latitude, 0.0001);
        $this->assertEqualsWithDelta(-79.3832, (float) $lead->longitude, 0.0001);
        $this->assertSame($payload['equipment_type_id'], $lead->equipment_type_id);
    }

    public function test_remarks_required_when_payment_status_pending(): void
    {
        $payload = $this->validLeadPayload();
        $payload['payment_status'] = LeadPaymentStatus::PENDING->value;
        unset($payload['remarks']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.leads.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['remarks']);
    }

    public function test_lead_status_mature_converts_to_client_in_transaction(): void
    {
        $lead = Lead::query()->create($this->validLeadPayload());

        $this->actingAs($this->admin)
            ->patchJson(route('admin.leads.status.update', $lead), [
                'status' => LeadStatus::MATURE->value,
            ])
            ->assertOk()
            ->assertJsonPath('converted', true);

        $lead->refresh();
        $this->assertTrue($lead->is_locked);
        $this->assertNotNull($lead->converted_at);
        $this->assertDatabaseHas('clients', [
            'lead_id' => $lead->id,
            'email' => $lead->email,
            'phone' => $lead->mobile_number,
        ]);
    }

    public function test_lead_status_won_converts_to_client(): void
    {
        $lead = Lead::query()->create($this->validLeadPayload());

        $this->actingAs($this->admin)
            ->patchJson(route('admin.leads.status.update', $lead), [
                'status' => LeadStatus::WON->value,
            ])
            ->assertOk()
            ->assertJsonPath('converted', true);

        $this->assertSame(1, Client::query()->where('lead_id', $lead->id)->count());
    }

    public function test_duplicate_customer_not_created_on_repeated_conversion(): void
    {
        $lead = Lead::query()->create($this->validLeadPayload());

        $this->actingAs($this->admin)
            ->patchJson(route('admin.leads.status.update', $lead), ['status' => LeadStatus::MATURE->value])
            ->assertOk();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.leads.status.update', $lead), ['status' => LeadStatus::WON->value])
            ->assertOk();

        $this->assertSame(1, Client::query()->where('lead_id', $lead->id)->count());
    }

    public function test_duplicate_client_by_email_is_linked_not_recreated(): void
    {
        $payload = $this->validLeadPayload();
        $payload['email'] = 'duplicate@example.com';

        Client::query()->create([
            'name' => 'Existing Client',
            'email' => 'duplicate@example.com',
            'phone' => '555-9999',
            'address' => '99 Existing Rd',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            're_completion_days' => LeadReCompletionDays::DAYS_14->value,
            'job_type' => LeadJobType::REGULAR->value,
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => LeadPaymentStatus::PENDING->value,
            'charges' => 10,
            'created_by' => $this->admin->id,
        ]);

        $lead = Lead::query()->create($payload);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.leads.status.update', $lead), ['status' => LeadStatus::MATURE->value])
            ->assertOk();

        $this->assertSame(1, Client::query()->where('email', 'duplicate@example.com')->count());
        $this->assertDatabaseHas('clients', [
            'email' => 'duplicate@example.com',
            'lead_id' => $lead->id,
        ]);
    }

    public function test_equipment_type_id_must_be_active(): void
    {
        $inactive = EquipmentType::query()->where('name', 'Red Mower')->firstOrFail();
        $inactive->update(['is_active' => false]);

        $payload = $this->validLeadPayload();
        $payload['equipment_type_id'] = $inactive->id;

        $this->actingAs($this->admin)
            ->postJson(route('admin.leads.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['equipment_type_id']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validLeadPayload(): array
    {
        return [
            'client_name' => 'Gorilla Property',
            'address' => '500 King St W, Toronto',
            'latitude' => '43.6532000',
            'longitude' => '-79.3832000',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            're_completion_days' => LeadReCompletionDays::DAYS_14->value,
            'job_type' => LeadJobType::REGULAR->value,
            'charges' => '120.00',
            'mobile_number' => '555-0100',
            'email' => 'gorilla@example.com',
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => LeadPaymentStatus::DONE->value,
            'remarks' => 'Side gate',
            'lead_date' => now()->toDateString(),
            'lead_time' => '09:00',
            'status' => LeadStatus::NEW->value,
        ];
    }
}
