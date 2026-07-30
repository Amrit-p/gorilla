<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobImageKind;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\UserEfficiency;
use App\Models\Checklist;
use App\Models\Client;
use App\Models\Job;
use App\Models\MowerChecklistSubmission;
use App\Models\User;
use App\Services\DashboardAnalyticsService;
use App\Services\JobImageManagementService;
use App\Services\JobMowerAssignmentService;
use App\Support\CrmConstants;
use App\Support\CrmRoles;
use App\Support\JobStoredImage;
use App\Support\QueryFilters\JobListFilter;
use App\Support\ServiceTypes;
use Database\Seeders\ChecklistSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CrmOptimizationTest extends TestCase
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

    public function test_crm_constants_and_global_helpers(): void
    {
        $this->assertSame(15, CrmConstants::defaultPagination());
        $this->assertContains(CrmConstants::JOB_LIST_SCOPE_TODAY, CrmConstants::jobListScopes());

        $request = Request::create('/admin/jobs', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $this->assertTrue(crm_wants_partial($request));
        $this->assertSame(crm_pagination(), CrmConstants::defaultPagination());
    }

    public function test_mower_workloads_uses_single_assignment_aggregate_query(): void
    {
        $mowerA = $this->mowerUser();
        $mowerB = $this->mowerUser();
        $client = $this->createClient();

        foreach ([$mowerA, $mowerB, $mowerA] as $mower) {
            $job = Job::query()->create($this->jobPayload($client->id));
            $job->assignedEmployees()->sync([$mower->id]);
        }

        $assignmentQueries = 0;
        DB::listen(function ($query) use (&$assignmentQueries): void {
            if (str_contains(strtolower($query->sql), 'job_user_assignments')) {
                $assignmentQueries++;
            }
        });

        $workloads = app(JobMowerAssignmentService::class)->mowerWorkloads(now()->toDateString());

        $this->assertGreaterThanOrEqual(2, count($workloads));
        $this->assertLessThanOrEqual(2, $assignmentQueries);
    }

    public function test_crm_index_endpoints_return_standard_ajax_html(): void
    {
        $client = $this->createClient();
        Job::query()->create($this->jobPayload($client->id));

        foreach ([
            route('admin.jobs.index'),
            route('admin.leads.index'),
            route('admin.users.index'),
        ] as $url) {
            $this->actingAs($this->admin)
                ->getJson($url, ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()
                ->assertJsonStructure(['html']);
        }
    }

    public function test_jobs_list_does_not_n_plus_one_clients(): void
    {
        $client = $this->createClient();

        for ($i = 0; $i < 8; $i++) {
            Job::query()->create($this->jobPayload($client->id, [
                'client_address' => "Address {$i}",
            ]));
        }

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index'))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $clientSelects = array_filter($queries, static fn (string $sql): bool => str_contains(strtolower($sql), 'from "clients"') || str_contains(strtolower($sql), 'from `clients`'));
        $this->assertLessThanOrEqual(2, count($clientSelects));
    }

    public function test_mower_dashboard_analytics_payload_is_stable(): void
    {
        $mower = $this->mowerUser();
        $client = $this->createClient();
        $job = Job::query()->create($this->jobPayload($client->id, [
            'status' => JobWorkflowStatus::COMPLETED->value,
            'consumed_time_minutes' => 45,
        ]));
        $job->assignedEmployees()->sync([$mower->id]);

        $service = app(DashboardAnalyticsService::class);
        $first = $service->mower($mower);
        $second = $service->mower($mower);

        $this->assertArrayHasKey('cards', $first);
        $this->assertSame($first['cards']['range_jobs']['value'], $second['cards']['range_jobs']['value']);
    }

    public function test_job_show_renders_readonly_field_photos(): void
    {
        $client = $this->createClient();
        $job = Job::query()->create($this->jobPayload($client->id));
        $job->forceFill([
            'before_images' => [
                (new JobStoredImage(
                    id: 'before-1',
                    path: 'jobs/'.$job->id.'/before/before-1.jpg',
                    thumbPath: 'jobs/'.$job->id.'/before/thumbs/before-1.jpg',
                    sizeBytes: 100,
                    uploadedAt: now()->toIso8601String(),
                ))->toArray(),
            ],
        ])->save();

        $presented = app(JobImageManagementService::class)->presentForJob($job->fresh(), JobImageKind::BEFORE);
        $this->assertNotEmpty($presented);

        $this->actingAs($this->admin)
            ->get(route('admin.jobs.show', $job))
            ->assertOk()
            ->assertSee('Field photos', false)
            ->assertSee('loading="lazy"', false)
            ->assertDontSee('mower-delete-image', false);
    }

    public function test_mower_status_validation_rejects_invalid_status(): void
    {
        $mower = $this->mowerUser();
        $this->completeChecklistForMower($mower);
        $job = Job::query()->create($this->jobPayload($this->createClient()->id));
        $job->assignedEmployees()->sync([$mower->id]);

        $this->actingAs($mower)
            ->patchJson(route('mower.jobs.status.update', $job), [
                'status' => 'Cancelled',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_sales_role_cannot_open_user_management(): void
    {
        $sales = User::factory()->create(['is_active' => true]);
        $sales->assignRole(CrmRoles::SALES_MANAGER);

        $this->actingAs($sales)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_mower_layout_uses_mobile_max_width(): void
    {
        $mower = $this->mowerUser();
        $this->completeChecklistForMower($mower);

        $this->actingAs($mower)
            ->get(route('mower.index'))
            ->assertOk()
            ->assertSee('max-w-lg', false);
    }

    public function test_job_list_filter_applies_today_scope(): void
    {
        $query = Job::query();
        app(JobListFilter::class)->apply($query, [
            'list_scope' => CrmConstants::JOB_LIST_SCOPE_TODAY,
        ]);

        $sql = $query->toSql();
        $this->assertStringContainsString('scheduled_date', $sql);
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

    private function mowerUser(): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'efficiency' => UserEfficiency::GOOD->value,
        ]);
        $user->assignRole(CrmRoles::MOWER);

        return $user;
    }

    private function createClient(): Client
    {
        return Client::query()->create([
            'name' => 'Optimization Client',
            'address' => '1 Opt Rd',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => 'No',
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'customer_type' => JobCustomerType::EASY->value,
            'charges' => 100,
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
            'client_address' => '1 Opt Rd',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
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
}
