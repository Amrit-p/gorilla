<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\UserEfficiency;
use App\Models\Client;
use App\Models\Job;
use App\Models\User;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MowerDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $mower;

    private User $otherMower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);

        $this->mower = User::factory()->create([
            'is_active' => true,
            'efficiency' => UserEfficiency::GOOD->value,
        ]);
        $this->mower->assignRole(CrmRoles::MOWER);

        $this->otherMower = User::factory()->create([
            'is_active' => true,
            'efficiency' => UserEfficiency::GOOD->value,
        ]);
        $this->otherMower->assignRole(CrmRoles::MOWER);
    }

    public function test_index_lists_only_assigned_jobs(): void
    {
        $assigned = $this->createAssignedJob($this->mower);
        $this->createAssignedJob($this->otherMower);

        $response = $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertOk()
            ->assertSee('My Jobs')
            ->assertSee($assigned->client?->name);

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, 'rounded-2xl border border-slate-200 bg-white p-4'));
    }

    public function test_index_scope_ajax_returns_partial(): void
    {
        $this->createAssignedJob($this->mower, [
            'scheduled_date' => now()->addWeek()->toDateString(),
            'status' => JobWorkflowStatus::STARTED->value,
        ]);

        $this->actingAs($this->mower)
            ->getJson(route('mower.index', ['scope' => 'upcoming']))
            ->assertOk()
            ->assertJsonStructure(['html']);
    }

    public function test_unassigned_job_show_is_forbidden(): void
    {
        $job = $this->createAssignedJob($this->otherMower);

        $this->actingAs($this->mower)
            ->get(route('mower.jobs.show', $job))
            ->assertForbidden();
    }

    public function test_mower_can_update_status_payment_and_time(): void
    {
        $job = $this->createAssignedJob($this->mower);

        $this->actingAs($this->mower)
            ->patchJson(route('mower.jobs.status.update', $job), [
                'status' => JobWorkflowStatus::HOLD->value,
            ])
            ->assertOk()
            ->assertJsonPath('status', JobWorkflowStatus::HOLD->value);

        $this->actingAs($this->mower)
            ->patchJson(route('mower.jobs.payment.update', $job), [
                'payment_status' => JobOperationalPaymentStatus::PENDING->value,
                'payment_pending_reason' => 'Customer not home',
            ])
            ->assertOk()
            ->assertJsonPath('payment_status', JobOperationalPaymentStatus::PENDING->value);

        $this->actingAs($this->mower)
            ->patchJson(route('mower.jobs.consumed-time.update', $job), [
                'consumed_time_minutes' => 75,
            ])
            ->assertOk()
            ->assertJsonPath('consumed_time_minutes', 75);

        $job->refresh();
        $this->assertSame(JobWorkflowStatus::HOLD->value, $job->status);
        $this->assertSame(JobOperationalPaymentStatus::PENDING->value, $job->payment_status);
        $this->assertSame(75, $job->consumed_time_minutes);
    }

    public function test_mower_can_upload_before_and_after_images(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->mower);

        $file = UploadedFile::fake()->image('before.jpg', 800, 600);

        $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.images.before', $job), [
                'images' => [$file],
            ])
            ->assertOk()
            ->assertJsonStructure(['images' => [['id', 'url', 'thumb_url', 'path']]]);

        $job->refresh();
        $this->assertCount(1, $job->before_images ?? []);
        Storage::disk('public')->assertExists($job->before_images[0]['path']);
        Storage::disk('public')->assertExists($job->before_images[0]['thumb_path']);

        $afterFile = UploadedFile::fake()->image('after.png', 400, 400);

        $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.images.after', $job), [
                'images' => [$afterFile],
            ])
            ->assertOk();

        $job->refresh();
        $this->assertCount(1, $job->after_images ?? []);
    }

    public function test_invalid_upload_is_rejected(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->mower);

        $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.images.before', $job), [
                'images' => [UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')],
            ])
            ->assertUnprocessable();
    }

    public function test_unassigned_mower_cannot_update_or_upload(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->otherMower);

        $this->actingAs($this->mower)
            ->patchJson(route('mower.jobs.status.update', $job), [
                'status' => JobWorkflowStatus::COMPLETED->value,
            ])
            ->assertForbidden();

        $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.images.before', $job), [
                'images' => [UploadedFile::fake()->image('x.jpg')],
            ])
            ->assertForbidden();
    }

    public function test_sales_manager_cannot_access_mower_dashboard(): void
    {
        $sales = User::factory()->create(['is_active' => true]);
        $sales->assignRole(CrmRoles::SALES_MANAGER);

        $this->actingAs($sales)
            ->get(route('mower.index'))
            ->assertForbidden();
    }

    public function test_job_show_page_has_mobile_sections(): void
    {
        $job = $this->createAssignedJob($this->mower);

        $this->actingAs($this->mower)
            ->get(route('mower.jobs.show', $job))
            ->assertOk()
            ->assertSee('Read-only')
            ->assertSee('Before photos')
            ->assertSee('Open in Maps', false);
    }

    public function test_employee_mobile_redirects_to_mower_dashboard(): void
    {
        $this->actingAs($this->mower)
            ->get(route('employee.mobile.index'))
            ->assertRedirect(route('mower.index'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createAssignedJob(User $mower, array $overrides = []): Job
    {
        $client = Client::query()->create([
            'name' => 'Mower Client '.$mower->id,
            'address' => '10 Field Rd',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => 'No',
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'customer_type' => JobCustomerType::EASY->value,
        ]);

        $job = Job::query()->create(array_merge([
            'client_id' => $client->id,
            'client_address' => '10 Field Rd',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'status' => JobWorkflowStatus::STARTED->value,
        ], $overrides));

        $job->assignedEmployees()->attach($mower->id, [
            'assignment_date' => $job->scheduled_date,
            'assignment_status' => $job->status,
        ]);

        return $job->load('client');
    }
}
