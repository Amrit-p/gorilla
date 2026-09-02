<?php

namespace Tests\Feature\Mower;

use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\Recurrence;
use App\Models\User;
use App\Notifications\JobAutoRescheduledNotification;
use App\Support\CrmConstants;
use App\Support\CrmRoles;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The next recurring job is now scheduled inline when a job transitions to
 * Completed (via App\Observers\JobObserver), replacing the old scheduled command.
 */
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

        $this->admin = User::query()->where('email', CrmConstants::ADMIN_EMAIL)->firstOrFail();
    }

    public function test_completing_recurring_job_creates_next_occurrence_with_correct_date(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'scheduled_date' => '2026-06-10',
        ]));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);

        $newJob = Job::query()
            ->where('rescheduled_from_job_id', $job->id)
            ->firstOrFail();

        $this->assertEquals('2026-06-17', $newJob->scheduled_date->toDateString());
        $this->assertEquals($recurrence->id, $newJob->recurrence_id);
        $this->assertSame(JobWorkflowStatus::PENDING->value, $newJob->status);

        $this->assertNotNull($job->fresh()->rescheduled_at);
    }

    public function test_completion_notifies_office_managers_and_done_by_user(): void
    {
        Notification::fake();

        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole(CrmRoles::OFFICE_MANAGER);

        $mower = User::factory()->create(['is_active' => true]);
        $mower->assignRole(CrmRoles::MOWER);

        $recurrence = Recurrence::query()->where('name', 'Monthly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'done_by_user_id' => $mower->id,
        ]));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);

        Notification::assertSentTo($manager, JobAutoRescheduledNotification::class);
        Notification::assertSentTo($mower, JobAutoRescheduledNotification::class);
    }

    public function test_one_time_recurrence_is_not_rescheduled(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'One-Time')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);

        $this->assertDatabaseCount('service_jobs', 1);
        $this->assertNull($job->fresh()->rescheduled_at);
    }

    public function test_completing_a_non_recurring_job_does_not_reschedule(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'is_recurring' => false,
        ]));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);

        $this->assertDatabaseCount('service_jobs', 1);
        Notification::assertNothingSent();
    }

    public function test_already_rescheduled_job_is_not_rescheduled_again(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'rescheduled_at' => now(),
        ]));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);

        $this->assertDatabaseCount('service_jobs', 1);
        Notification::assertNothingSent();
    }

    public function test_bi_weekly_recurrence_adds_two_weeks(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Bi-Weekly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'scheduled_date' => '2026-06-10',
        ]));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);

        $newJob = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();

        $this->assertEquals('2026-06-24', $newJob->scheduled_date->toDateString());
    }

    public function test_holding_a_completed_job_removes_its_pending_next_job(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'scheduled_date' => '2026-06-10',
        ]));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);
        $next = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();

        $job->update(['status' => JobWorkflowStatus::HOLD->value]);

        $this->assertSoftDeleted('service_jobs', ['id' => $next->id]);
        $this->assertNull($job->fresh()->rescheduled_at);
    }

    public function test_reverting_a_completed_job_to_pending_removes_its_pending_next_job(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'scheduled_date' => '2026-06-10',
        ]));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);
        $next = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();

        $job->update(['status' => JobWorkflowStatus::PENDING->value]);

        $this->assertSoftDeleted('service_jobs', ['id' => $next->id]);
        $this->assertNull($job->fresh()->rescheduled_at);
    }

    public function test_next_job_is_kept_when_it_is_no_longer_pending(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'scheduled_date' => '2026-06-10',
        ]));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);
        $next = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();

        // The mower has already started the next job, so it must not be removed.
        $next->update(['status' => JobWorkflowStatus::STARTED->value]);

        $job->update(['status' => JobWorkflowStatus::HOLD->value]);

        $this->assertDatabaseHas('service_jobs', ['id' => $next->id, 'deleted_at' => null]);
        $this->assertNotNull($job->fresh()->rescheduled_at);
    }

    public function test_recompleting_after_a_hold_recreates_the_next_job(): void
    {
        Notification::fake();

        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        $job = Job::query()->create($this->jobPayload($recurrence->id, [
            'scheduled_date' => '2026-06-10',
        ]));

        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);
        $job->update(['status' => JobWorkflowStatus::HOLD->value]);
        $job->update(['status' => JobWorkflowStatus::COMPLETED->value]);

        $liveNext = Job::query()
            ->where('rescheduled_from_job_id', $job->id)
            ->where('status', JobWorkflowStatus::PENDING->value)
            ->get();

        $this->assertCount(1, $liveNext);
        $this->assertNotNull($job->fresh()->rescheduled_at);
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
