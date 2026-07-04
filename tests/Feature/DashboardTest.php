<?php

namespace Tests\Feature;

use App\Enums\JobWorkflowStatus;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
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
            'status' => JobWorkflowStatus::STARTED->value,
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

    public function test_calendar_search_matches_client_customer_unique_id_and_assigned_employee(): void
    {
        $mower = User::query()->create([
            'name' => 'Search Mower',
            'email' => 'search-mower@mowingcrm.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $client = Client::query()->create([
            'name' => 'Filter Client',
            'address' => '5 Pine St',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'charges' => 85,
        ]);

        $job = Job::query()->create([
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
            'status' => JobWorkflowStatus::STARTED->value,
            'created_by' => $this->admin->id,
        ]);

        $job->assignedEmployees()->attach($mower->id, ['assignment_date' => now()->toDateString()]);

        $this->actingAs($this->admin)
            ->get(route('dashboard.daily-jobs-table', [
                'date' => now()->toDateString(),
                'search' => (string) $client->customer_unique_id,
            ]))
            ->assertOk()
            ->assertSee('Filter Client');

        $this->actingAs($this->admin)
            ->get(route('dashboard.daily-jobs-table', [
                'date' => now()->toDateString(),
                'search' => 'Search Mower',
            ]))
            ->assertOk()
            ->assertSee('Filter Client');

        $this->actingAs($this->admin)
            ->get(route('dashboard.three-week-grid', ['search' => 'Search Mower']))
            ->assertOk()
            ->assertSee('1 job');

        $this->actingAs($this->admin)
            ->get(route('dashboard.three-week-grid', ['search' => 'Nonexistent Term']))
            ->assertOk()
            ->assertDontSee('1 job');
    }

    public function test_daily_jobs_table_supports_the_day_panels_extra_filters(): void
    {
        $client = Client::query()->create([
            'name' => 'Assigned Client',
            'address' => '9 Elm St',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'charges' => 85,
        ]);

        $assignedJob = Job::query()->create([
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
            'status' => JobWorkflowStatus::PENDING->value,
            'created_by' => $this->admin->id,
        ]);

        $mower = User::query()->create([
            'name' => 'Assigned Mower',
            'email' => 'assigned-mower@mowingcrm.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $assignedJob->assignedEmployees()->attach($mower->id, ['assignment_date' => now()->toDateString()]);

        Job::query()->create([
            'client_id' => $client->id,
            'client_address' => $client->address,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '11:00',
            'estimated_duration_minutes' => 45,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
            'payment_mode' => 'Online',
            'payment_status' => 'Received',
            'status' => JobWorkflowStatus::PENDING->value,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard.daily-jobs-table', [
                'date' => now()->toDateString(),
                'assignment' => 'unassigned',
            ]))
            ->assertOk()
            ->assertDontSee('Assigned Mower');

        $this->actingAs($this->admin)
            ->get(route('dashboard.daily-jobs-table', [
                'date' => now()->toDateString(),
                'assignment' => 'assigned',
                'payment_mode' => 'Cash',
            ]))
            ->assertOk()
            ->assertSee('Assigned Mower');
    }

    public function test_daily_jobs_table_date_range_widens_beyond_the_clicked_day(): void
    {
        $client = Client::query()->create([
            'name' => 'Range Client',
            'address' => '12 Birch Ave',
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
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'scheduled_time' => '09:30',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
            'payment_mode' => 'Cash',
            'payment_status' => 'Received',
            'status' => JobWorkflowStatus::PENDING->value,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard.daily-jobs-table', [
                'date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertDontSee('Range Client');

        $this->actingAs($this->admin)
            ->get(route('dashboard.daily-jobs-table', [
                'date' => now()->toDateString(),
                'date_range' => [
                    'start' => now()->toDateString(),
                    'end' => now()->addDays(3)->toDateString(),
                ],
            ]))
            ->assertOk()
            ->assertSee('Range Client');
    }

    public function test_three_week_grid_and_daily_leads_table_exclude_leads_converted_to_customer(): void
    {
        Lead::query()->create([
            'client_name' => 'Open Lead',
            'address' => '3 Cedar St',
            'status' => LeadStatus::NEW->value,
            'lead_date' => now()->toDateString(),
        ]);

        Lead::query()->create([
            'client_name' => 'Converted Lead',
            'address' => '4 Cedar St',
            'status' => LeadStatus::WON->value,
            'lead_date' => now()->toDateString(),
            'converted_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard.three-week-grid'))
            ->assertOk()
            ->assertSee('1 lead')
            ->assertDontSee('2 leads');

        $this->actingAs($this->admin)
            ->get(route('dashboard.daily-leads-table', [
                'date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Open Lead')
            ->assertDontSee('Converted Lead');
    }
}
