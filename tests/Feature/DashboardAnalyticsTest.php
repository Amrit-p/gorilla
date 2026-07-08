<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Enums\UserEfficiency;
use App\Models\Checklist;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\Lead;
use App\Models\MowerChecklistSubmission;
use App\Models\User;
use App\Services\DashboardAnalyticsService;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\ChecklistSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->seed(ChecklistSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    public function test_admin_dashboard_shows_revenue_and_job_metrics(): void
    {
        $client = $this->createClient(150.00);
        Job::query()->create($this->jobPayload($client->id, [
            'status' => JobWorkflowStatus::COMPLETED->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'scheduled_date' => now()->toDateString(),
        ]));

        Job::query()->create($this->jobPayload($client->id, [
            'payment_status' => JobOperationalPaymentStatus::PENDING->value,
            'scheduled_date' => now()->toDateString(),
        ]));

        Lead::query()->create($this->leadPayload(LeadStatus::NEW->value));
        Lead::query()->create($this->leadPayload(LeadStatus::WON->value));

        $this->actingAs($this->admin)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Total revenue (MTD)', false)
            ->assertSee('Pending payments', false)
            ->assertSee('Jobs today', false)
            ->assertSee('Completed jobs (MTD)', false)
            ->assertSee('Mower performance', false)
            ->assertSee('$150.00', false);
    }

    public function test_sales_dashboard_shows_conversion_metrics(): void
    {
        $sales = $this->userWithRole(CrmRoles::SALES_MANAGER);

        Lead::query()->create($this->leadPayload(LeadStatus::NEW->value));
        Lead::query()->create($this->leadPayload(LeadStatus::MATURE->value));
        Lead::query()->create($this->leadPayload(LeadStatus::WON->value));

        $this->actingAs($sales)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Jobs today', false);
    }

    public function test_mower_dashboard_shows_field_metrics(): void
    {
        $mower = $this->userWithRole(CrmRoles::MOWER);
        $client = $this->createClient(80);

        $job = Job::query()->create($this->jobPayload($client->id, [
            'scheduled_date' => now()->toDateString(),
            'status' => JobWorkflowStatus::STARTED->value,
            'consumed_time_minutes' => null,
        ]));
        $job->assignedEmployees()->attach($mower->id, [
            'assignment_date' => $job->scheduled_date,
            'assignment_status' => $job->status,
        ]);

        $done = Job::query()->create($this->jobPayload($client->id, [
            'scheduled_date' => now()->toDateString(),
            'status' => JobWorkflowStatus::COMPLETED->value,
            'consumed_time_minutes' => 120,
        ]));
        $done->assignedEmployees()->attach($mower->id, [
            'assignment_date' => $done->scheduled_date,
            'assignment_status' => $done->status,
        ]);

        $this->actingAs($mower)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Today', false)
            ->assertSee('Completed hours', false)
            ->assertSee('Total upcoming', false);

        $this->completeChecklistForMower($mower);

        $this->actingAs($mower)
            ->get(route('mower.index'))
            ->assertOk()
            ->assertSee('Hours', false)
            ->assertSee('Total Upcoming', false);
    }

    public function test_mower_performance_table_ajax_filters_by_date_range(): void
    {
        $mower = $this->userWithRole(CrmRoles::MOWER);
        $client = $this->createClient(100);

        $inRange = Job::query()->create($this->jobPayload($client->id, [
            'scheduled_date' => now()->toDateString(),
            'status' => JobWorkflowStatus::COMPLETED->value,
            'consumed_time_minutes' => 90,
        ]));
        $inRange->assignedEmployees()->attach($mower->id, [
            'assignment_date' => $inRange->scheduled_date,
            'assignment_status' => $inRange->status,
        ]);

        $outOfRange = Job::query()->create($this->jobPayload($client->id, [
            'scheduled_date' => now()->subMonths(2)->toDateString(),
            'status' => JobWorkflowStatus::COMPLETED->value,
            'consumed_time_minutes' => 60,
        ]));
        $outOfRange->assignedEmployees()->attach($mower->id, [
            'assignment_date' => $outOfRange->scheduled_date,
            'assignment_status' => $outOfRange->status,
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard.mower-performance-table', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($mower->name)
            ->assertSee('1.5h', false);

        $this->actingAs($this->admin)
            ->get(route('dashboard.mower-performance-table', [
                'start_date' => now()->subMonths(3)->toDateString(),
                'end_date' => now()->subMonths(3)->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('No completed jobs in this range.', false)
            ->assertDontSee($mower->name);
    }

    public function test_analytics_service_caches_admin_payload(): void
    {
        $service = app(DashboardAnalyticsService::class);
        $first = $service->admin();
        $second = $service->admin();

        $this->assertSame($first['cards']['jobs_today']['value'], $second['cards']['jobs_today']['value']);
    }

    private function completeChecklistForMower(User $mower): void
    {
        $checklist = Checklist::safetyChecklist();
        $today = now()->toDateString();

        foreach ($checklist->points as $point) {
            MowerChecklistSubmission::create([
                'user_id' => $mower->id,
                'checklist_point_id' => $point->id,
                'date' => $today,
            ]);
        }
    }

    private function userWithRole(string $roleLabel): User
    {
        $user = User::factory()->create(['is_active' => true, 'efficiency' => UserEfficiency::GOOD->value]);
        $user->assignRole($roleLabel);

        return $user;
    }

    private function createClient(float $charges): Client
    {
        return Client::query()->create([
            'name' => 'Analytics Client',
            'address' => '1 Analytics Rd',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'customer_type' => JobCustomerType::EASY->value,
            'charges' => $charges,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function jobPayload(int $clientId, array $overrides = []): array
    {
        return array_merge([
            'client_id' => $clientId,
            'client_address' => '1 Analytics Rd',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'status' => JobWorkflowStatus::STARTED->value,
            'created_by' => $this->admin->id,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function leadPayload(string $status): array
    {
        return [
            'client_name' => 'Lead '.$status,
            'address' => '2 Lead St',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'payment_mode' => 'Cash',
            'payment_status' => LeadPaymentStatus::DONE->value,
            'status' => $status,
        ];
    }
}
