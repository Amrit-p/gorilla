<?php

namespace Tests\Feature\Mower;

use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\Recurrence;
use App\Models\User;
use App\Support\CrmConstants;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillJobRescheduleTest extends TestCase
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

    public function test_creates_missing_next_occurrence_for_completed_recurring_job(): void
    {
        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();
        $job = $this->completedJob($recurrence->id, ['scheduled_date' => '2026-09-05']);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $newJob = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();
        $this->assertEquals('2026-09-12', $newJob->scheduled_date->toDateString());
        $this->assertSame(JobWorkflowStatus::PENDING->value, $newJob->status);
        $this->assertNotNull($job->fresh()->rescheduled_at);
    }

    public function test_backfills_a_custom_named_recurrence_with_stored_interval_days(): void
    {
        $recurrence = Recurrence::create(['name' => '3-Weekly', 'interval_days' => 21, 'is_active' => true, 'sort_order' => 1]);
        $job = $this->completedJob($recurrence->id, ['scheduled_date' => '2026-09-05']);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $newJob = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();
        $this->assertEquals('2026-09-26', $newJob->scheduled_date->toDateString());
    }

    public function test_skips_recurrence_with_no_interval_days_set(): void
    {
        $recurrence = Recurrence::create(['name' => 'Custom Pattern', 'is_active' => true, 'sort_order' => 1]);
        $this->completedJob($recurrence->id);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 1);
    }

    public function test_dry_run_reports_without_creating_anything(): void
    {
        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();
        $job = $this->completedJob($recurrence->id);

        $this->artisan('app:backfill-job-reschedule', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 1);
        $this->assertNull($job->fresh()->rescheduled_at);
    }

    public function test_skips_jobs_whose_recurrence_cannot_be_resolved(): void
    {
        $recurrence = Recurrence::query()->where('name', 'One-Time')->firstOrFail();
        $this->completedJob($recurrence->id);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 1);
    }

    public function test_ignores_jobs_already_rescheduled_with_a_live_next_job(): void
    {
        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();
        $job = $this->completedJob($recurrence->id, ['rescheduled_at' => now()]);
        $this->completedJob($recurrence->id, [
            'status' => JobWorkflowStatus::PENDING->value,
            'rescheduled_from_job_id' => $job->id,
        ]);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 2);
    }

    public function test_reconciles_stuck_rescheduled_at_and_creates_the_missing_next_occurrence(): void
    {
        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        // rescheduled_at set from a past completion, but the next job it once
        // created is gone (deleted outside the normal Hold/Pending flow) —
        // nothing points back to this job, so it's stuck with no recovery path.
        $job = $this->completedJob($recurrence->id, [
            'scheduled_date' => '2026-09-05',
            'rescheduled_at' => '2026-07-30 12:31:49',
        ]);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $newJob = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();
        $this->assertEquals('2026-09-12', $newJob->scheduled_date->toDateString());
        $this->assertNotNull($job->fresh()->rescheduled_at);
    }

    public function test_dry_run_reports_stuck_jobs_without_clearing_them(): void
    {
        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();
        $job = $this->completedJob($recurrence->id, [
            'scheduled_date' => '2026-09-05',
            'rescheduled_at' => '2026-07-30 12:31:49',
        ]);

        $this->artisan('app:backfill-job-reschedule', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 1);
        $this->assertSame('2026-07-30 12:31:49', $job->fresh()->rescheduled_at->format('Y-m-d H:i:s'));
    }

    public function test_leaves_rescheduled_at_alone_when_its_next_job_still_exists(): void
    {
        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();
        $job = $this->completedJob($recurrence->id, [
            'scheduled_date' => '2026-09-05',
            'rescheduled_at' => '2026-07-30 12:31:49',
        ]);
        $next = $this->completedJob($recurrence->id, [
            'status' => JobWorkflowStatus::PENDING->value,
            'scheduled_date' => '2026-09-26',
        ]);
        $next->update(['rescheduled_from_job_id' => $job->id]);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $this->assertSame('2026-07-30 12:31:49', $job->fresh()->rescheduled_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseCount('service_jobs', 2);
    }

    public function test_ignores_jobs_scheduled_on_or_before_the_default_cutoff(): void
    {
        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();
        $job = $this->completedJob($recurrence->id, ['scheduled_date' => '2026-08-15']);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 1);
        $this->assertNull($job->fresh()->rescheduled_at);
    }

    public function test_after_option_overrides_the_default_cutoff(): void
    {
        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();
        $job = $this->completedJob($recurrence->id, ['scheduled_date' => '2026-08-15']);

        $this->artisan('app:backfill-job-reschedule', ['--after' => '2026-08-01'])->assertSuccessful();

        $newJob = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();
        $this->assertEquals('2026-08-22', $newJob->scheduled_date->toDateString());
    }

    public function test_invalid_after_option_fails_cleanly(): void
    {
        $this->artisan('app:backfill-job-reschedule', ['--after' => 'not-a-date'])
            ->assertFailed();
    }

    /** @param array<string, mixed> $overrides */
    private function completedJob(int $recurrenceId, array $overrides = []): Job
    {
        return Job::query()->create(array_merge([
            'customer_name' => 'Backfill Client',
            'phone' => '555-0000',
            'email' => 'backfill.client@example.com',
            'client_address' => '1 Backfill Rd',
            'scheduled_date' => '2026-09-10',
            'is_recurring' => true,
            'recurrence_id' => $recurrenceId,
            'status' => JobWorkflowStatus::COMPLETED->value,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
