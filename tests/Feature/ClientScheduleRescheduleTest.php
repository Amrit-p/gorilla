<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\Recurrence;
use App\Models\User;
use App\Notifications\JobRescheduledNotification;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClientScheduleRescheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->seed(RecurrenceSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    public function test_updating_client_schedule_date_reschedules_pending_jobs_and_notifies_mower(): void
    {
        Notification::fake();

        $officeManager = User::factory()->create(['is_active' => true]);
        $officeManager->assignRole(CrmRoles::OFFICE_MANAGER);

        $mower = User::factory()->create(['is_active' => true]);
        $mower->assignRole(CrmRoles::MOWER);

        $client = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));

        $pendingJob = Job::query()->create(array_merge($this->jobPayload($client->id), [
            'status' => JobWorkflowStatus::PENDING->value,
            'done_by_user_id' => $mower->id,
        ]));

        $newDate = now()->addWeek()->toDateString();

        $this->actingAs($this->admin)
            ->patch(route('admin.clients.update', $client), $this->clientPayload($newDate))
            ->assertRedirect(route('admin.clients.show', $client));

        $this->assertSame($newDate, $pendingJob->refresh()->scheduled_date->toDateString());

        Notification::assertSentTo($officeManager, JobRescheduledNotification::class);
        Notification::assertSentTo($mower, JobRescheduledNotification::class);
    }

    public function test_updating_client_schedule_date_does_not_touch_completed_or_verified_jobs(): void
    {
        Notification::fake();

        $client = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));

        $completedJob = Job::query()->create(array_merge($this->jobPayload($client->id), [
            'status' => JobWorkflowStatus::COMPLETED->value,
        ]));

        $verifiedJob = Job::query()->create(array_merge($this->jobPayload($client->id), [
            'status' => JobWorkflowStatus::PENDING->value,
            'verified_at' => now(),
            'verified_by' => $this->admin->id,
        ]));

        $originalCompletedDate = $completedJob->scheduled_date->toDateString();
        $originalVerifiedDate = $verifiedJob->scheduled_date->toDateString();
        $newDate = now()->addWeek()->toDateString();

        $this->actingAs($this->admin)
            ->patch(route('admin.clients.update', $client), $this->clientPayload($newDate))
            ->assertRedirect(route('admin.clients.show', $client));

        $this->assertSame($originalCompletedDate, $completedJob->refresh()->scheduled_date->toDateString());
        $this->assertSame($originalVerifiedDate, $verifiedJob->refresh()->scheduled_date->toDateString());

        Notification::assertNotSentTo(User::query()->find($this->admin->id), JobRescheduledNotification::class);
    }

    public function test_updating_client_without_changing_schedule_date_does_not_reschedule_jobs(): void
    {
        Notification::fake();

        $scheduleDate = now()->addDays(2)->toDateString();
        $client = Client::query()->create($this->clientPayload($scheduleDate));

        $pendingJob = Job::query()->create(array_merge($this->jobPayload($client->id), [
            'status' => JobWorkflowStatus::PENDING->value,
        ]));

        $originalJobDate = $pendingJob->scheduled_date->toDateString();

        $this->actingAs($this->admin)
            ->patch(route('admin.clients.update', $client), $this->clientPayload($scheduleDate))
            ->assertRedirect(route('admin.clients.show', $client));

        $this->assertSame($originalJobDate, $pendingJob->refresh()->scheduled_date->toDateString());
        Notification::assertNothingSent();
    }

    public function test_updating_client_schedule_date_reschedules_hold_jobs(): void
    {
        Notification::fake();

        $client = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));

        $holdJob = Job::query()->create(array_merge($this->jobPayload($client->id), [
            'status' => JobWorkflowStatus::HOLD->value,
        ]));

        $newDate = now()->addWeek()->toDateString();

        $this->actingAs($this->admin)
            ->patch(route('admin.clients.update', $client), $this->clientPayload($newDate))
            ->assertRedirect(route('admin.clients.show', $client));

        $this->assertSame($newDate, $holdJob->refresh()->scheduled_date->toDateString());
    }

    public function test_bulk_reschedule_updates_schedule_date_and_reschedules_jobs(): void
    {
        Notification::fake();

        $clientA = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));
        $clientB = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));

        $jobA = Job::query()->create(array_merge($this->jobPayload($clientA->id), [
            'status' => JobWorkflowStatus::PENDING->value,
        ]));
        $jobB = Job::query()->create(array_merge($this->jobPayload($clientB->id), [
            'status' => JobWorkflowStatus::HOLD->value,
        ]));

        $newDate = now()->addWeeks(2)->toDateString();

        $this->actingAs($this->admin)
            ->postJson(route('admin.clients.reschedule'), [
                'client_ids' => [$clientA->id, $clientB->id],
                'scheduled_date' => $newDate,
                'scheduled_time' => '10:30',
            ])
            ->assertOk();

        $this->assertSame($newDate, $clientA->refresh()->schedule_date->toDateString());
        $this->assertSame($newDate, $clientB->refresh()->schedule_date->toDateString());
        $this->assertSame($newDate, $jobA->refresh()->scheduled_date->toDateString());
        $this->assertSame($newDate, $jobB->refresh()->scheduled_date->toDateString());
        $this->assertSame('10:30', substr((string) $jobA->scheduled_time, 0, 5));
    }

    public function test_bulk_reschedule_creates_a_job_for_a_customer_without_jobs(): void
    {
        Notification::fake();

        $client = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));
        $this->assertSame(0, $client->jobs()->count());

        $newDate = now()->addWeek()->toDateString();

        $this->actingAs($this->admin)
            ->postJson(route('admin.clients.reschedule'), [
                'client_ids' => [$client->id],
                'scheduled_date' => $newDate,
            ])
            ->assertOk();

        $job = $client->jobs()->firstOrFail();
        $this->assertSame(1, $client->jobs()->count());
        $this->assertSame($newDate, $job->scheduled_date->toDateString());
    }

    public function test_bulk_reschedule_rejects_a_past_date(): void
    {
        $client = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));

        $this->actingAs($this->admin)
            ->postJson(route('admin.clients.reschedule'), [
                'client_ids' => [$client->id],
                'scheduled_date' => now()->subDay()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('scheduled_date');
    }

    public function test_bulk_hold_moves_pending_unverified_jobs_to_hold(): void
    {
        Notification::fake();

        $client = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));

        $pendingJob = Job::query()->create(array_merge($this->jobPayload($client->id), [
            'status' => JobWorkflowStatus::PENDING->value,
        ]));
        $verifiedJob = Job::query()->create(array_merge($this->jobPayload($client->id), [
            'status' => JobWorkflowStatus::PENDING->value,
            'verified_at' => now(),
            'verified_by' => $this->admin->id,
        ]));
        $completedJob = Job::query()->create(array_merge($this->jobPayload($client->id), [
            'status' => JobWorkflowStatus::COMPLETED->value,
        ]));

        $this->actingAs($this->admin)
            ->postJson(route('admin.clients.hold'), ['client_ids' => [$client->id]])
            ->assertOk();

        $this->assertSame(JobWorkflowStatus::HOLD->value, $pendingJob->refresh()->status);
        $this->assertSame(JobWorkflowStatus::PENDING->value, $verifiedJob->refresh()->status);
        $this->assertSame(JobWorkflowStatus::COMPLETED->value, $completedJob->refresh()->status);
    }

    public function test_creating_client_creates_a_pending_job_from_the_customer(): void
    {
        Notification::fake();

        $scheduleDate = now()->addDays(3)->toDateString();

        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $this->clientPayload($scheduleDate))
            ->assertRedirect();

        $client = Client::query()->latest('id')->firstOrFail();
        $job = $client->jobs()->firstOrFail();

        $this->assertSame(1, $client->jobs()->count());
        $this->assertSame(JobWorkflowStatus::PENDING->value, $job->status);
        $this->assertSame($scheduleDate, $job->scheduled_date->toDateString());
        $this->assertSame($client->service_types, $job->required_services);
    }

    public function test_updating_schedule_date_creates_a_job_when_customer_has_no_jobs(): void
    {
        Notification::fake();

        $client = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));
        $this->assertSame(0, $client->jobs()->count());

        $newDate = now()->addWeek()->toDateString();

        $this->actingAs($this->admin)
            ->patch(route('admin.clients.update', $client), $this->clientPayload($newDate))
            ->assertRedirect(route('admin.clients.show', $client));

        $job = $client->jobs()->firstOrFail();

        $this->assertSame(1, $client->jobs()->count());
        $this->assertSame(JobWorkflowStatus::PENDING->value, $job->status);
        $this->assertSame($newDate, $job->scheduled_date->toDateString());
    }

    public function test_creating_client_rejects_a_past_schedule_date(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $this->clientPayload(now()->subDay()->toDateString()))
            ->assertSessionHasErrors('schedule_date');

        $this->assertSame(0, Client::query()->count());
    }

    public function test_updating_client_rejects_a_past_schedule_date(): void
    {
        $client = Client::query()->create($this->clientPayload(now()->addDays(2)->toDateString()));

        $this->actingAs($this->admin)
            ->patch(route('admin.clients.update', $client), $this->clientPayload(now()->subDay()->toDateString()))
            ->assertSessionHasErrors('schedule_date');
    }

    /**
     * @return array<string, mixed>
     */
    private function clientPayload(string $scheduleDate): array
    {
        return [
            'name' => 'Reschedule Test Client',
            'address' => '10 Reschedule Road',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => 'No',
            'recurrence_id' => Recurrence::query()->where('is_active', true)->value('id'),
            'job_type' => 'Regular',
            'schedule_date' => $scheduleDate,
            'payment_mode' => 'Cash',
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
            'client_address' => '10 Reschedule Road',
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            'created_by' => $this->admin->id,
        ];
    }
}
