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
