<?php

namespace Tests\Feature;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\MowerPayout;
use App\Models\User;
use App\Support\CrmConstants;
use App\Support\CrmRoles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Payouts are attached directly here, bypassing SalaryCalculatorService, to
 * reproduce jobs paid out before a payout started verifying its jobs.
 */
class FixPaidOutJobsVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::query()->where('email', CrmConstants::ADMIN_EMAIL)->firstOrFail();

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);
    }

    public function test_verifies_completed_paid_out_jobs_as_the_payout_creator_on_the_payout_day(): void
    {
        $creator = User::factory()->create(['is_active' => true]);
        $job = $this->job();
        $this->payout([$job], createdBy: $creator, createdAt: '2026-08-26 12:24:33');

        $this->artisan('app:fix-paid-out-jobs-verification')->assertSuccessful();

        $job->refresh();
        $this->assertTrue($job->isVerified());
        $this->assertSame($creator->id, $job->verified_by);
        $this->assertSame('2026-08-26', $job->verified_date->toDateString());
        $this->assertNull($job->verified_amount);
        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $job->payment_status);
        $this->assertTrue($job->hasMowerPayout());
    }

    public function test_falls_back_to_the_admin_when_the_payout_has_no_creator(): void
    {
        $job = $this->job();
        $this->payout([$job], createdBy: null);

        $this->artisan('app:fix-paid-out-jobs-verification')->assertSuccessful();

        $this->assertSame($this->admin->id, $job->refresh()->verified_by);
    }

    public function test_releases_a_job_walked_back_from_completed_and_keeps_its_payout_for_the_other_jobs(): void
    {
        $walkedBack = $this->job(['status' => JobWorkflowStatus::PENDING->value]);
        $stillCompleted = $this->job();
        $payout = $this->payout([$walkedBack, $stillCompleted]);

        $this->artisan('app:fix-paid-out-jobs-verification')
            ->expectsOutputToContain('Kept, amount unchanged')
            ->assertSuccessful();

        $this->assertFalse($walkedBack->fresh()->hasMowerPayout());
        $this->assertFalse($walkedBack->fresh()->isVerified());
        $this->assertDatabaseHas('mower_payouts', ['id' => $payout->id, 'amount' => 150]);
        $this->assertTrue($stillCompleted->fresh()->hasMowerPayout());
        $this->assertTrue($stillCompleted->fresh()->isVerified());
    }

    public function test_deletes_a_payout_left_with_no_jobs(): void
    {
        $walkedBack = $this->job(['status' => JobWorkflowStatus::PENDING->value]);
        $payout = $this->payout([$walkedBack]);

        $this->artisan('app:fix-paid-out-jobs-verification')
            ->expectsOutputToContain('Deleted - no jobs left')
            ->assertSuccessful();

        $this->assertDatabaseMissing('mower_payouts', ['id' => $payout->id]);
        $this->assertFalse($walkedBack->fresh()->hasMowerPayout());
    }

    public function test_keeps_a_payout_whose_remaining_job_is_soft_deleted(): void
    {
        $walkedBack = $this->job(['status' => JobWorkflowStatus::PENDING->value]);
        $trashed = $this->job();
        $payout = $this->payout([$walkedBack, $trashed]);
        $trashed->delete();

        $this->artisan('app:fix-paid-out-jobs-verification')->assertSuccessful();

        $this->assertDatabaseHas('mower_payouts', ['id' => $payout->id]);
        $this->assertDatabaseHas('mower_payout_job', ['mower_payout_id' => $payout->id, 'job_id' => $trashed->id]);
    }

    public function test_leaves_already_verified_jobs_untouched(): void
    {
        $job = $this->job();
        $job->forceFill([
            'verified_at' => now(),
            'verified_by' => $this->admin->id,
            'verified_date' => '2026-08-01',
            'verified_amount' => 100,
            'verification_comment' => 'Cash counted.',
        ])->save();
        $this->payout([$job]);

        $this->artisan('app:fix-paid-out-jobs-verification')
            ->expectsOutputToContain('Every paid-out job is already Completed and verified.')
            ->assertSuccessful();

        $job->refresh();
        $this->assertSame('2026-08-01', $job->verified_date->toDateString());
        $this->assertSame('Cash counted.', $job->verification_comment);
    }

    public function test_dry_run_previews_without_changing_anything(): void
    {
        $completed = $this->job();
        $walkedBack = $this->job(['status' => JobWorkflowStatus::PENDING->value]);
        $payout = $this->payout([$completed]);
        $emptied = $this->payout([$walkedBack]);

        $this->artisan('app:fix-paid-out-jobs-verification --dry-run')
            ->expectsOutputToContain('[Dry run] Would verify 1 Completed paid-out job(s)')
            ->expectsOutputToContain('[Dry run] Would release 1 paid-out job(s)')
            ->assertSuccessful();

        $this->assertFalse($completed->fresh()->isVerified());
        $this->assertTrue($walkedBack->fresh()->hasMowerPayout());
        $this->assertDatabaseHas('mower_payouts', ['id' => $payout->id]);
        $this->assertDatabaseHas('mower_payouts', ['id' => $emptied->id]);
    }

    /**
     * @param  array<int, Job>  $jobs
     */
    private function payout(array $jobs, ?User $createdBy = null, ?string $createdAt = null): MowerPayout
    {
        $payout = MowerPayout::query()->create([
            'user_id' => $this->mower->id,
            'amount' => 150,
            'bonus' => 0,
            'created_by' => $createdBy?->id,
        ]);

        if ($createdAt !== null) {
            $payout->forceFill(['created_at' => $createdAt])->save();
        }

        $payout->jobs()->attach(array_map(fn (Job $job): int => $job->id, $jobs));

        return $payout;
    }

    /** @param array<string, mixed> $overrides */
    private function job(array $overrides = []): Job
    {
        return Job::query()->create(array_merge([
            'customer_name' => 'Payout Client',
            'phone' => '555-9000',
            'client_address' => '1 Payout Rd',
            'scheduled_date' => '2026-08-20',
            'status' => JobWorkflowStatus::COMPLETED->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'charges' => 100,
            'done_by_user_id' => $this->mower->id,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
