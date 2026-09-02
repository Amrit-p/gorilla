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
        $job = $this->completedJob($recurrence->id, ['scheduled_date' => '2026-06-10']);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $newJob = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();
        $this->assertEquals('2026-06-17', $newJob->scheduled_date->toDateString());
        $this->assertSame(JobWorkflowStatus::PENDING->value, $newJob->status);
        $this->assertNotNull($job->fresh()->rescheduled_at);
    }

    public function test_fixes_previously_unparseable_hyphenated_recurrence(): void
    {
        $recurrence = Recurrence::create(['name' => '3-Weekly', 'is_active' => true, 'sort_order' => 1]);
        $job = $this->completedJob($recurrence->id, ['scheduled_date' => '2026-06-10']);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $newJob = Job::query()->where('rescheduled_from_job_id', $job->id)->firstOrFail();
        $this->assertEquals('2026-07-01', $newJob->scheduled_date->toDateString());
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

    public function test_ignores_jobs_already_rescheduled(): void
    {
        $recurrence = Recurrence::query()->where('name', 'Weekly')->firstOrFail();
        $this->completedJob($recurrence->id, ['rescheduled_at' => now()]);

        $this->artisan('app:backfill-job-reschedule')->assertSuccessful();

        $this->assertDatabaseCount('service_jobs', 1);
    }

    /** @param array<string, mixed> $overrides */
    private function completedJob(int $recurrenceId, array $overrides = []): Job
    {
        return Job::query()->create(array_merge([
            'customer_name' => 'Backfill Client',
            'phone' => '555-0000',
            'email' => 'backfill.client@example.com',
            'client_address' => '1 Backfill Rd',
            'scheduled_date' => now()->toDateString(),
            'is_recurring' => true,
            'recurrence_id' => $recurrenceId,
            'status' => JobWorkflowStatus::COMPLETED->value,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
