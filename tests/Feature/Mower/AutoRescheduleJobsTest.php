<?php

namespace Tests\Feature\Mower;

use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\Recurrence;
use App\Models\User;
use App\Notifications\JobAutoRescheduledNotification;
use App\Support\CrmRoles;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AutoRescheduleJobsTest extends TestCase
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

    public function test_completed_recurring_job_creates_new_job_with_correct_date(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'scheduled_date' => '2026-06-10',
            'status' => JobWorkflowStatus::COMPLETED->value,
        ]));

        $this->artisan('app:auto-reschedule-jobs')->assertSuccessful();

        $newJob = Job::query()
            ->where('customer_name', 'Test Client')
            ->where('phone', '555-0000')
            ->where('status', JobWorkflowStatus::PENDING->value)
            ->whereNull('rescheduled_at')
            ->firstOrFail();

        $this->assertEquals('2026-06-17', $newJob->scheduled_date->toDateString());
        $this->assertEquals($recurrence->id, $newJob->recurrence_id);

        $job->refresh();
        $this->assertNotNull($job->rescheduled_at);
    }

    public function test_command_notifies_office_managers_and_done_by_user(): void
    {
        Notification::fake();

        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole(CrmRoles::OFFICE_MANAGER);

        $mower = User::factory()->create(['is_active' => true]);
        $mower->assignRole(CrmRoles::MOWER);

        $recurrence = Recurrence::query()->where('name', 'Monthly')->firstOrFail();

        Job::query()->create($this->jobPayload($recurrence->id, [
            'status' => JobWorkflowStatus::COMPLETED->value,
            'done_by_user_id' => $mower->id,
        ]));

        $this->artisan('app:auto-reschedule-jobs')->assertSuccessful();

        Notification::assertSentTo($manager, JobAutoRescheduledNotification::class);
        Notification::assertSentTo($mower, JobAutoRescheduledNotification::class);
    }

    public function test_one_time_recurrence_is_skipped(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'One-Time')->firstOrFail();

        Job::query()->create($this->jobPayload($recurrence->id, [
            'status' => JobWorkflowStatus::COMPLETED->value,
        ]));

        $this->artisan('app:auto-reschedule-jobs')->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 1);
    }

    public function test_already_rescheduled_job_is_not_processed_again(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        Job::query()->create($this->jobPayload($recurrence->id, [
            'status' => JobWorkflowStatus::COMPLETED->value,
            'rescheduled_at' => now(),
        ]));

        $this->artisan('app:auto-reschedule-jobs')->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 1);
        Notification::assertNothingSent();
    }

    public function test_non_completed_job_is_not_rescheduled(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        foreach ([JobWorkflowStatus::PENDING, JobWorkflowStatus::STARTED, JobWorkflowStatus::HOLD] as $status) {
            Job::query()->create($this->jobPayload($recurrence->id, [
                'status' => $status->value,
            ]));
        }

        $this->artisan('app:auto-reschedule-jobs')->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 3);
        Notification::assertNothingSent();
    }

    public function test_bi_weekly_recurrence_adds_two_weeks(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Bi-Weekly')->firstOrFail();

        Job::query()->create($this->jobPayload($recurrence->id, [
            'scheduled_date' => '2026-06-10',
            'status' => JobWorkflowStatus::COMPLETED->value,
        ]));

        $this->artisan('app:auto-reschedule-jobs')->assertSuccessful();

        $newJob = Job::query()
            ->where('status', JobWorkflowStatus::PENDING->value)
            ->whereNull('rescheduled_at')
            ->firstOrFail();

        $this->assertEquals('2026-06-24', $newJob->scheduled_date->toDateString());
    }

    /** @param array<string, mixed> $overrides */
    private function jobPayload(int $recurrenceId, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Test Client',
            'phone' => '555-0000',
            'email' => 'test.client@example.com',
            'client_address' => '1 Test Rd',
            'scheduled_date' => now()->toDateString(),
            'is_recurring' => true,
            'recurrence_id' => $recurrenceId,
            'status' => JobWorkflowStatus::PENDING->value,
            'created_by' => $this->admin->id,
        ], $overrides);
    }
}
