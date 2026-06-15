<?php

namespace Tests\Feature\Mower;

use App\Models\Checklist;
use App\Models\Client;
use App\Models\Job;
use App\Models\MowerChecklistSubmission;
use App\Models\Recurrence;
use App\Models\User;
use App\Notifications\MowerClientJobCreatedNotification;
use App\Support\CrmRoles;
use Database\Seeders\ChecklistSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MowerClientControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $mower;

    private Recurrence $recurrence;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(ChecklistSeeder::class);

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);
        $this->completeChecklistForMower($this->mower);

        $this->recurrence = Recurrence::create(['name' => 'Weekly', 'is_active' => true, 'sort_order' => 1]);
    }

    public function test_mower_can_view_create_client_page(): void
    {
        $response = $this->actingAs($this->mower)->get(route('mower.clients.create'));

        $response->assertOk();
        $response->assertViewIs('mower.clients.create');
    }

    public function test_unauthenticated_user_cannot_view_create_client_page(): void
    {
        $response = $this->get(route('mower.clients.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_mower_can_create_client_and_job_is_auto_created(): void
    {
        Storage::fake('public');
        Notification::fake();

        $officeManager = User::factory()->create(['is_active' => true]);
        $officeManager->assignRole(CrmRoles::OFFICE_MANAGER);

        $salesManager = User::factory()->create(['is_active' => true]);
        $salesManager->assignRole(CrmRoles::SALES_MANAGER);

        $response = $this->actingAs($this->mower)->post(route('mower.clients.store'), [
            'address' => '123 Test Street, Auckland',
            'service_types' => ['Mulching'],
            'weed_spray' => 'Yes',
            'recurrence_id' => $this->recurrence->id,
            'job_type' => 'Regular',
            'payment_mode' => 'Cash',
            'customer_type' => 'Easy',
            'documents' => [UploadedFile::fake()->create('site-photo.jpg', 100, 'image/jpeg')],
        ]);

        $response->assertRedirect(route('mower.index'));
        $response->assertSessionHas('success');

        $client = Client::where('address', '123 Test Street, Auckland')->first();
        $this->assertNotNull($client);
        $this->assertEquals($this->mower->id, $client->created_by);

        $this->assertDatabaseHas('service_jobs', [
            'client_id' => $client->id,
            'payment_status' => 'Pending',
            'done_by_user_id' => $this->mower->id,
        ]);

        $job = Job::where('client_id', $client->id)->first();
        Notification::assertSentTo($officeManager, MowerClientJobCreatedNotification::class,
            fn ($n) => $n->job->id === $job->id && $n->createdBy->id === $this->mower->id
        );
        Notification::assertSentTo($salesManager, MowerClientJobCreatedNotification::class,
            fn ($n) => $n->job->id === $job->id && $n->createdBy->id === $this->mower->id
        );
        Notification::assertNotSentTo($this->mower, MowerClientJobCreatedNotification::class);
    }

    public function test_mower_cannot_create_client_without_required_fields(): void
    {
        $response = $this->actingAs($this->mower)->post(route('mower.clients.store'), []);

        $response->assertSessionHasErrors(['address', 'service_types', 'weed_spray', 'recurrence_id', 'job_type', 'payment_mode', 'customer_type']);
    }

    public function test_non_mower_cannot_create_client(): void
    {
        $admin = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('mower.clients.store'), [
            'address' => '123 Test Street',
            'service_types' => ['Mulching'],
            'weed_spray' => 'Yes',
            'recurrence_id' => $this->recurrence->id,
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
