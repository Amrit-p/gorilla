<?php

namespace Tests\Feature\Mower;

use App\Models\Checklist;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\MowerChecklistSubmission;
use App\Models\Recurrence;
use App\Models\User;
use App\Notifications\MowerClientJobCreatedNotification;
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
        $this->equipmentTypeId = (int) EquipmentType::query()->where('is_active', true)->value('id');
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
            'phone' => '0211234567',
            'customer_name' => 'Test Customer',
            'service_types' => ['Mulching'],
            'weed_spray' => 'Yes',
            'recurrence_id' => $this->recurrence->id,
            'equipment_type_id' => $this->equipmentTypeId,
            'job_type' => 'Regular',
            'payment_mode' => 'Cash',
            'customer_type' => 'Easy',
            'schedule_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('mower.index'));
        $response->assertSessionHas('success');

        $this->assertSame(0, Client::query()->count());
        $this->assertDatabaseHas('service_jobs', [
            'client_address' => '123 Test Street, Auckland',
            'phone' => '0211234567',
            'customer_name' => 'Test Customer',
            'payment_status' => 'Pending',
            'done_by_user_id' => $this->mower->id,
            'client_id' => null,
        ]);

        $job = Job::query()->where('phone', '0211234567')->first();
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

        $response->assertSessionHasErrors(['address', 'phone', 'service_types', 'weed_spray', 'recurrence_id', 'equipment_type_id', 'job_type', 'payment_mode', 'customer_type']);
    }

    public function test_non_mower_cannot_create_job(): void
    {
        $admin = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('mower.jobs.store'), [
            'address' => '123 Test Street',
            'phone' => '0211234567',
            'service_types' => ['Mulching'],
            'weed_spray' => 'Yes',
            'recurrence_id' => $this->recurrence->id,
            'equipment_type_id' => $this->equipmentTypeId,
            'job_type' => 'Regular',
            'payment_mode' => 'Cash',
            'customer_type' => 'Easy',
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
