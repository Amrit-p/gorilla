<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\UserEfficiency;
use App\Http\Middleware\EnsureMowerChecklistComplete;
use App\Models\Job;
use App\Models\MowerRemark;
use App\Models\User;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MowerRemarkJobIdTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->withoutMiddleware(EnsureMowerChecklistComplete::class);

        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
        $this->mower = User::factory()->create([
            'is_active' => true,
            'efficiency' => UserEfficiency::GOOD->value,
        ]);
        $this->mower->assignRole(CrmRoles::MOWER);
    }

    public function test_store_remark_persists_job_id_without_requiring_client(): void
    {
        $job = $this->createAssignedJob();

        $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.remark.store', $job), [
                'description' => 'Customer left gate open.',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Remark saved.');

        $this->assertDatabaseHas('mower_remarks', [
            'job_id' => $job->id,
            'user_id' => $this->mower->id,
            'description' => 'Customer left gate open.',
        ]);
    }

    public function test_job_show_lists_last_remark_by_job_id(): void
    {
        $job = $this->createAssignedJob();

        MowerRemark::query()->create([
            'user_id' => $this->mower->id,
            'job_id' => $job->id,
            'description' => 'Earlier note.',
        ]);
        MowerRemark::query()->create([
            'user_id' => $this->mower->id,
            'job_id' => $job->id,
            'description' => 'Latest note for this job.',
        ]);

        $this->actingAs($this->mower)
            ->get(route('mower.jobs.show', $job))
            ->assertOk()
            ->assertSee('Latest note for this job.');
    }

    public function test_admin_client_remarks_endpoint_filters_by_job_id(): void
    {
        $job = $this->createAssignedJob();
        $other = $this->createAssignedJob(['customer_name' => 'Other Customer', 'phone' => '555-9999']);

        MowerRemark::query()->create([
            'user_id' => $this->mower->id,
            'job_id' => $job->id,
            'description' => 'Target job remark.',
        ]);
        MowerRemark::query()->create([
            'user_id' => $this->mower->id,
            'job_id' => $other->id,
            'description' => 'Other job remark.',
        ]);

        $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.client-remarks', ['job_id' => $job->id]))
            ->assertOk()
            ->assertJsonPath('lastRemark.description', 'Target job remark.')
            ->assertJsonCount(1, 'allRemarks');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createAssignedJob(array $overrides = []): Job
    {
        $job = Job::query()->create(array_merge([
            'client_id' => null,
            'customer_name' => 'Remark Customer',
            'phone' => '555-0100',
            'client_address' => '10 Remark Rd',
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
        ], $overrides));

        $job->assignedEmployees()->attach($this->mower->id, [
            'assignment_date' => $job->scheduled_date,
            'assignment_status' => $job->status,
        ]);

        return $job;
    }
}
