<?php

namespace Tests\Feature\Mower;

use App\Models\Checklist;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\MowerChecklistSubmission;
use App\Models\Recurrence;
use App\Models\User;
use App\Notifications\MowerClientJobCreatedNotification;
use App\Services\JobManagementService;
use App\Support\CrmRoles;
use Database\Seeders\ChecklistSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MowerClientControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $mower;

    private Recurrence $recurrence;

    private int $equipmentTypeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(ChecklistSeeder::class);
        $this->seed(MasterCatalogSeeder::class);

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);
        $this->completeChecklistForMower($this->mower);

        $this->recurrence = Recurrence::create(['name' => 'Weekly', 'is_active' => true, 'sort_order' => 1]);
        $this->equipmentTypeId = (int) EquipmentType::query()->where('name', 'Red Mower')->value('id');
    }

    public function test_mower_can_view_create_job_page(): void
    {
        $response = $this->actingAs($this->mower)->get(route('mower.jobs.create'));

        $response->assertOk();
        $response->assertViewIs('mower.jobs.create');
    }

    public function test_unauthenticated_user_cannot_view_create_job_page(): void
    {
        $response = $this->get(route('mower.jobs.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_mower_can_create_job_without_customer_record(): void
    {
        Notification::fake();

        $officeManager = User::factory()->create(['is_active' => true]);
        $officeManager->assignRole(CrmRoles::OFFICE_MANAGER);

        $salesManager = User::factory()->create(['is_active' => true]);
        $salesManager->assignRole(CrmRoles::SALES_MANAGER);

        $response = $this->actingAs($this->mower)->post(route('mower.jobs.store'), [
            'address' => '123 Test Street, Auckland',
            'service_types' => ['Mulching'],
            'weed_spray' => 'Yes',
            'recurrence_id' => $this->recurrence->id,
            'equipment_type_id' => $this->equipmentTypeId,
            'van_number' => 'VAN-3',
            'payment_mode' => 'Cash',
            'charges' => 120.50,
            'customer_type' => 'Easy',
            'special_remarks' => 'Gate code 1234, mow the back lawn first.',
            'status' => 'Hold',
            'schedule_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('mower.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('service_jobs', [
            'client_address' => '123 Test Street, Auckland',
            'customer_name' => '123 Test Street, Auckland',
            'payment_status' => 'Pending',
            'phone' => null,
            'zone_id' => null,
            'van_number' => 'VAN-3',
            'equipment_type_id' => $this->equipmentTypeId,
            'special_remarks' => 'Gate code 1234, mow the back lawn first.',
            'status' => 'Hold',
            'site_instructions' => null,
            'done_by_user_id' => $this->mower->id,
        ]);

        $job = Job::query()->where('client_address', '123 Test Street, Auckland')->first();
        $this->assertNotNull($job);
        $this->assertSame(60, (int) $job->estimated_duration_minutes);
        Notification::assertSentTo($officeManager, MowerClientJobCreatedNotification::class,
            fn ($n) => $n->job->id === $job->id && $n->createdBy->id === $this->mower->id
        );
        Notification::assertSentTo($salesManager, MowerClientJobCreatedNotification::class,
            fn ($n) => $n->job->id === $job->id && $n->createdBy->id === $this->mower->id
        );
        Notification::assertNotSentTo($this->mower, MowerClientJobCreatedNotification::class);
    }

    public function test_mower_cannot_create_job_without_required_fields(): void
    {
        $response = $this->actingAs($this->mower)->post(route('mower.jobs.store'), []);

        $response->assertSessionHasErrors(['address', 'service_types', 'weed_spray', 'recurrence_id', 'equipment_type_id', 'van_number', 'payment_mode', 'customer_type', 'special_remarks', 'status']);
        $response->assertSessionDoesntHaveErrors(['job_type', 'phone', 'additional_site_instructions']);
    }

    public function test_amount_is_required_for_cash_and_both_payment_modes(): void
    {
        foreach (['Cash', 'Both'] as $paymentMode) {
            $response = $this->actingAs($this->mower)->post(
                route('mower.jobs.store'),
                $this->validPayload(['payment_mode' => $paymentMode, 'charges' => null])
            );

            $response->assertSessionHasErrors('charges');
        }
    }

    public function test_amount_is_optional_for_online_payment_mode(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->mower)->post(
            route('mower.jobs.store'),
            $this->validPayload(['payment_mode' => 'Online', 'charges' => null])
        );

        $response->assertRedirect(route('mower.index'));
        $this->assertDatabaseHas('service_jobs', [
            'payment_mode' => 'Online',
            'charges' => null,
        ]);
    }

    public function test_mower_can_only_pick_red_or_yellow_mower_equipment(): void
    {
        $blockedEquipmentId = (int) EquipmentType::query()->where('name', 'Honda')->value('id');

        $response = $this->actingAs($this->mower)->post(
            route('mower.jobs.store'),
            $this->validPayload(['equipment_type_id' => $blockedEquipmentId])
        );

        $response->assertSessionHasErrors('equipment_type_id');

        $yellowMowerId = (int) EquipmentType::query()->where('name', 'Yellow Mower')->value('id');

        $response = $this->actingAs($this->mower)->post(
            route('mower.jobs.store'),
            $this->validPayload(['equipment_type_id' => $yellowMowerId])
        );

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('service_jobs', [
            'equipment_type_id' => $yellowMowerId,
        ]);
    }

    public function test_create_job_page_only_offers_red_and_yellow_mower_equipment(): void
    {
        $response = $this->actingAs($this->mower)->get(route('mower.jobs.create'));

        $response->assertOk();
        $this->assertSame(
            ['Red Mower', 'Yellow Mower'],
            array_column($response->viewData('equipmentTypes'), 'name')
        );
        $response->assertDontSee('name="job_type"', false);
        $response->assertDontSee('name="zone_id"', false);
        $response->assertDontSee('name="customer_name"', false);
        $response->assertDontSee('name="phone"', false);
        $response->assertDontSee('name="additional_site_instructions"', false);
        $response->assertSee('name="van_number"', false);
        $response->assertSee('name="status"', false);
        $response->assertSee('Comments');
    }

    public function test_property_address_is_the_first_section_on_the_create_page(): void
    {
        $response = $this->actingAs($this->mower)->get(route('mower.jobs.create'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertLessThan(
            strpos($content, 'Services'),
            strpos($content, 'Property Address'),
            'Property Address should be rendered before the Services section.'
        );
        $response->assertSee('id="job-address-input"', false);
        $response->assertSee('regionCodes: ["nz"]', false);
    }

    public function test_mower_created_job_is_visible_to_the_office_manager_in_the_admin_list(): void
    {
        Notification::fake();

        $officeManager = User::factory()->create(['is_active' => true]);
        $officeManager->assignRole(CrmRoles::OFFICE_MANAGER);

        $this->actingAs($this->mower)
            ->post(route('mower.jobs.store'), $this->validPayload())
            ->assertSessionHasNoErrors();

        $job = Job::query()->where('client_address', '123 Test Street, Auckland')->firstOrFail();
        $this->assertSame('VAN-3', $job->van_number);

        $this->actingAs($officeManager)
            ->get(route('admin.jobs.index'))
            ->assertOk()
            ->assertSee('123 Test Street, Auckland', false);
    }

    public function test_failed_job_creation_reports_an_error_instead_of_success(): void
    {
        Notification::fake();

        $this->mock(JobManagementService::class, function ($mock): void {
            $mock->shouldReceive('createJob')->once()->andReturnNull();
        });

        $response = $this->actingAs($this->mower)->post(route('mower.jobs.store'), $this->validPayload());

        $response->assertRedirect(route('mower.jobs.create'));
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');
        Notification::assertNothingSent();
    }

    public function test_job_status_must_be_a_valid_workflow_status(): void
    {
        $response = $this->actingAs($this->mower)->post(
            route('mower.jobs.store'),
            $this->validPayload(['status' => 'Nonsense'])
        );

        $response->assertSessionHasErrors('status');
    }

    public function test_created_job_is_assigned_to_the_logged_in_mower(): void
    {
        Notification::fake();

        $otherMower = User::factory()->create(['is_active' => true]);
        $otherMower->assignRole(CrmRoles::MOWER);

        $this->actingAs($this->mower)->post(route('mower.jobs.store'), $this->validPayload());

        $job = Job::query()->where('client_address', '123 Test Street, Auckland')->firstOrFail();

        $this->assertSame($this->mower->id, $job->done_by_user_id);
        $this->assertSame($this->mower->id, $job->created_by);
        $this->assertNotSame($otherMower->id, $job->done_by_user_id);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'address' => '123 Test Street, Auckland',
            'service_types' => ['Mulching'],
            'weed_spray' => 'Yes',
            'recurrence_id' => $this->recurrence->id,
            'equipment_type_id' => $this->equipmentTypeId,
            'van_number' => 'VAN-3',
            'payment_mode' => 'Cash',
            'charges' => 120.50,
            'customer_type' => 'Easy',
            'special_remarks' => 'Gate code 1234.',
            'status' => 'Pending',
            'schedule_date' => now()->toDateString(),
        ], $overrides);
    }

    public function test_non_mower_cannot_create_job(): void
    {
        $admin = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('mower.jobs.store'), [
            'address' => '123 Test Street',
            'service_types' => ['Mulching'],
            'weed_spray' => 'Yes',
            'recurrence_id' => $this->recurrence->id,
            'equipment_type_id' => $this->equipmentTypeId,
            'van_number' => 'VAN-3',
            'payment_mode' => 'Cash',
            'charges' => 90,
            'customer_type' => 'Easy',
            'special_remarks' => 'Front lawn only.',
            'status' => 'Pending',
        ]);

        $response->assertForbidden();
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
}
