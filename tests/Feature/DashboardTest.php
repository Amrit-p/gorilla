<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\LeadPaymentStatus;
use App\Models\EquipmentType;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
use App\Enums\LeadWeedSpray;
use App\Models\Client;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_dashboard_shows_real_metrics_not_placeholders(): void
    {
        Lead::query()->create([
            'client_name' => 'Today Lead',
            'address' => '1 Main St',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            'payment_status' => LeadPaymentStatus::PENDING->value,
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'payment_mode' => 'Cash',
            'charges' => 120,
            'status' => LeadStatus::NEW->value,
            'created_at' => now(),
        ]);

        $client = Client::query()->create([
            'name' => 'Schedule Client',
            'address' => '2 Oak Rd',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'charges' => 85,
        ]);

        Job::query()->create([
            'client_id' => $client->id,
            'client_address' => $client->address,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:30',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
            'payment_mode' => 'Cash',
            'payment_status' => 'Received',
            'status' => \App\Enums\JobWorkflowStatus::STARTED->value,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Schedule Client')
            ->assertSee('Total revenue (MTD)', false)
            ->assertSee('Today', false)
            ->assertSee('schedule', false)
            ->assertSee('Lead pipeline')
            ->assertDontSee('Placeholder Client');
    }
}
