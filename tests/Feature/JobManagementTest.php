<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\UserEfficiency;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\JobLevel;
use App\Models\Recurrence;
use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\JobLevelSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->seed(RecurrenceSeeder::class);
        $this->seed(JobLevelSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
        $this->mower = User::factory()->create([
            'is_active' => true,
            'efficiency' => UserEfficiency::GOOD->value,
        ]);
        $this->mower->assignRole(CrmRoles::MOWER);
    }

    public function test_job_can_be_created_via_ajax(): void
    {
        $client = Client::query()->create($this->clientPayload());

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.store'), $this->jobPayload($client->id))
            ->assertCreated()
            ->assertJsonPath('job.client_id', $client->id);

        $this->assertDatabaseHas('service_jobs', [
            'client_id' => $client->id,
            'status' => JobWorkflowStatus::PENDING->value,
        ]);
    }

    public function test_mower_assignment_on_create(): void
    {
        $client = Client::query()->create($this->clientPayload());

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.store'), array_merge($this->jobPayload($client->id), [
                'employee_ids' => [$this->mower->id],
                'done_by_user_id' => $this->mower->id,
            ]))
            ->assertCreated();

        $job = Job::query()->where('client_id', $client->id)->firstOrFail();
        $this->assertTrue($job->assignedEmployees()->where('users.id', $this->mower->id)->exists());
        $this->assertSame($this->mower->id, $job->done_by_user_id);
    }

    public function test_status_update_uses_workflow_statuses(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.status.update'), [
                'job_ids' => [$job->id],
                'status' => JobWorkflowStatus::HOLD->value,
            ])
            ->assertOk()
            ->assertJsonPath('updated_count', 1);

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.status.update'), [
                'job_ids' => [$job->id],
                'status' => JobWorkflowStatus::COMPLETED->value,
            ])
            ->assertOk();

        $job->refresh();
        $this->assertSame(JobWorkflowStatus::COMPLETED->value, $job->status);
    }

    public function test_list_filter_today_returns_scheduled_jobs(): void
    {
        $client = Client::query()->create($this->clientPayload());
        Job::query()->create(array_merge($this->jobPayload($client->id), [
            'scheduled_date' => now()->toDateString(),
            'status' => JobWorkflowStatus::STARTED->value,
        ]));
        Job::query()->create(array_merge($this->jobPayload($client->id), [
            'scheduled_date' => now()->addWeek()->toDateString(),
            'status' => JobWorkflowStatus::STARTED->value,
        ]));

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['list_scope' => 'today']))
            ->assertOk();

        $html = (string) $response->json('html');
        $this->assertStringContainsString(now()->format('d M'), $html);
    }

    public function test_mower_suggestions_endpoint_returns_recommendation(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.mower-suggestions', [
                'scheduled_date' => now()->toDateString(),
                'estimated_duration_minutes' => 90,
            ]))
            ->assertOk()
            ->assertJsonStructure(['suggestion', 'workloads']);
    }

    public function test_mower_cannot_create_jobs_but_can_view(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->mower)
            ->get(route('admin.jobs.index'))
            ->assertOk();

        $this->actingAs($this->mower)
            ->get(route('admin.jobs.create'))
            ->assertForbidden();
    }

    public function test_job_show_page_renders_timeline(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.status.update'), [
                'job_ids' => [$job->id],
                'status' => JobWorkflowStatus::HOLD->value,
            ]);

        $this->actingAs($this->admin)
            ->get(route('admin.jobs.show', $job))
            ->assertOk()
            ->assertSee('Activity timeline')
            ->assertSee('Job tracking');
    }

    public function test_bulk_schedule_updates_scheduled_date(): void
    {
        $job = $this->createJob();
        $newDate = now()->addDays(3)->toDateString();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$job->id],
                'scheduled_date' => $newDate,
            ])
            ->assertOk()
            ->assertJsonPath('updated_count', 1)
            ->assertJsonPath('message', 'Job rescheduled successfully.');

        $job->refresh();
        $this->assertSame($newDate, $job->scheduled_date->toDateString());
    }

    public function test_bulk_schedule_updates_scheduled_time_when_provided(): void
    {
        $job = $this->createJob();
        $newDate = now()->addDays(2)->toDateString();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$job->id],
                'scheduled_date' => $newDate,
                'scheduled_time' => '14:30',
            ])
            ->assertOk();

        $job->refresh();
        $this->assertSame($newDate, $job->scheduled_date->toDateString());
        $this->assertSame('14:30', $job->scheduled_time);
    }

    public function test_bulk_schedule_multiple_jobs(): void
    {
        $jobA = $this->createJob();
        $jobB = $this->createJob();
        $newDate = now()->addWeek()->toDateString();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$jobA->id, $jobB->id],
                'scheduled_date' => $newDate,
            ])
            ->assertOk()
            ->assertJsonPath('updated_count', 2)
            ->assertJsonPath('message', 'Selected jobs rescheduled successfully.');

        $this->assertSame($newDate, $jobA->refresh()->scheduled_date->toDateString());
        $this->assertSame($newDate, $jobB->refresh()->scheduled_date->toDateString());
    }

    public function test_mower_cannot_bulk_schedule_jobs(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->mower)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$job->id],
                'scheduled_date' => now()->addDay()->toDateString(),
            ])
            ->assertForbidden();
    }

    private function createJob(): Job
    {
        $client = Client::query()->create($this->clientPayload());

        return Job::query()->create(array_merge($this->jobPayload($client->id), [
            'created_by' => $this->admin->id,
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function clientPayload(): array
    {
        return [
            'name' => 'Job Client',
            'address' => '50 Job Lane',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => 'No',
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'customer_type' => 'Easy',
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function jobPayload(int $clientId): array
    {
        return [
            'client_id' => $clientId,
            'client_address' => '50 Job Lane',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            'recurrence_id' => Recurrence::query()->where('is_active', true)->value('id'),
            'job_level_id' => JobLevel::query()->where('is_active', true)->value('id'),
        ];
    }
}
