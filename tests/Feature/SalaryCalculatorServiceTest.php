<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\MowerPayout;
use App\Models\User;
use App\Services\SalaryCalculatorService;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SalaryCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mower;

    private SalaryCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole(CrmRoles::OFFICE_MANAGER);

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);

        $this->service = $this->app->make(SalaryCalculatorService::class);
    }

    public function test_monthly_breakdown_returns_twelve_rows_aggregated_from_completed_jobs(): void
    {
        $this->createJob('2026-03-10', charges: 200, minutes: 90);
        $this->createJob('2026-03-22', charges: 300, minutes: 30);
        // Different month, so it must not leak into March.
        $this->createJob('2026-04-02', charges: 999, minutes: 60);

        $rows = $this->service->monthlyBreakdown($this->mower, 2026);

        $this->assertCount(12, $rows);

        $march = $rows[2];
        $this->assertSame('March', $march->month_label);
        $this->assertSame(500.0, $march->total_sales);
        $this->assertSame(2.0, $march->total_hours);

        $this->assertSame(999.0, $rows[3]->total_sales);
        $this->assertSame(0.0, $rows[0]->total_sales);
    }

    public function test_monthly_breakdown_ignores_jobs_that_are_not_completed(): void
    {
        $this->createJob('2026-03-10', charges: 200, minutes: 60, status: 'Pending');

        $rows = $this->service->monthlyBreakdown($this->mower, 2026);

        $this->assertSame(0.0, $rows[2]->total_sales);
        $this->assertSame(0.0, $rows[2]->total_hours);
    }

    public function test_monthly_breakdown_reports_payout_and_bonus_from_linked_payouts(): void
    {
        $job = $this->createJob('2026-05-12', charges: 400, minutes: 120);

        $this->service->createPayout($this->mower, [$job->id], 250.0, 40.0, 'May run', $this->admin);

        $rows = $this->service->monthlyBreakdown($this->mower, 2026);

        $this->assertSame(250.0, $rows[4]->total_payout);
        $this->assertSame(40.0, $rows[4]->total_bonus);
        $this->assertSame(1, $rows[4]->payout_count);
        // Sales and hours still come from the jobs themselves.
        $this->assertSame(400.0, $rows[4]->total_sales);
        $this->assertSame(2.0, $rows[4]->total_hours);
    }

    public function test_payouts_for_month_returns_only_that_months_payouts_newest_first(): void
    {
        $mayJob = $this->createJob('2026-05-12', charges: 400, minutes: 120);
        $mayEarly = $this->service->createPayout($this->mower, [$mayJob->id], 100.0, 0.0, 'First', $this->admin);

        $maySecondJob = $this->createJob('2026-05-20', charges: 200, minutes: 60);
        $mayLate = $this->service->createPayout($this->mower, [$maySecondJob->id], 150.0, 0.0, 'Second', $this->admin);
        // Force a later creation timestamp so the ordering is deterministic.
        $mayLate->forceFill(['created_at' => $mayEarly->created_at->copy()->addMinute()])->save();

        $juneJob = $this->createJob('2026-06-01', charges: 300, minutes: 60);
        $this->service->createPayout($this->mower, [$juneJob->id], 250.0, 0.0, null, $this->admin);

        $payouts = $this->service->payoutsForMonth($this->mower, 2026, 5);

        $this->assertCount(2, $payouts);
        $this->assertSame([$mayLate->id, $mayEarly->id], $payouts->pluck('id')->all());
        // Creator and job count come loaded for the modal.
        $this->assertSame($this->admin->id, $payouts->first()->creator->id);
        $this->assertSame(1, $payouts->first()->jobs_count);
    }

    public function test_create_payout_records_the_payout_and_links_every_selected_job(): void
    {
        $first = $this->createJob('2026-06-01', charges: 100, minutes: 60);
        $second = $this->createJob('2026-06-08', charges: 150, minutes: 30);

        $payout = $this->service->createPayout(
            $this->mower,
            [$first->id, $second->id],
            220.5,
            10.0,
            'Fortnight',
            $this->admin
        );

        $this->assertDatabaseHas('mower_payouts', [
            'id' => $payout->id,
            'user_id' => $this->mower->id,
            'amount' => 220.5,
            'bonus' => 10.0,
            'comment' => 'Fortnight',
            'created_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('mower_payout_job', ['mower_payout_id' => $payout->id, 'job_id' => $first->id]);
        $this->assertDatabaseHas('mower_payout_job', ['mower_payout_id' => $payout->id, 'job_id' => $second->id]);
        $this->assertTrue($first->fresh()->hasMowerPayout());
    }

    public function test_create_payout_rejects_a_job_that_is_already_paid(): void
    {
        $job = $this->createJob('2026-06-01', charges: 100, minutes: 60);
        $this->service->createPayout($this->mower, [$job->id], 90.0, 0.0, null, $this->admin);

        $this->expectException(ValidationException::class);

        $this->service->createPayout($this->mower, [$job->id], 90.0, 0.0, null, $this->admin);
    }

    public function test_create_payout_rejects_a_job_belonging_to_another_mower(): void
    {
        $otherMower = User::factory()->create(['is_active' => true]);
        $otherMower->assignRole(CrmRoles::MOWER);

        $job = $this->createJob('2026-06-01', charges: 100, minutes: 60, doneBy: $otherMower);

        $this->expectException(ValidationException::class);

        $this->service->createPayout($this->mower, [$job->id], 90.0, 0.0, null, $this->admin);
    }

    public function test_jobs_for_month_returns_only_that_months_completed_jobs_with_a_paid_flag(): void
    {
        $paid = $this->createJob('2026-07-04', charges: 100, minutes: 60);
        $unpaid = $this->createJob('2026-07-19', charges: 100, minutes: 60);
        $this->createJob('2026-08-19', charges: 100, minutes: 60);

        $this->service->createPayout($this->mower, [$paid->id], 80.0, 0.0, null, $this->admin);

        $jobs = $this->service->jobsForMonth($this->mower, 2026, 7);

        $this->assertSame(2, $jobs->total());
        $byId = $jobs->getCollection()->keyBy('id');
        $this->assertNotNull($byId[$paid->id]->mower_payout_id);
        $this->assertNull($byId[$unpaid->id]->mower_payout_id);
    }

    public function test_mower_payout_scopes_split_jobs_by_payout_existence(): void
    {
        $paid = $this->createJob('2026-09-01', charges: 100, minutes: 60);
        $unpaid = $this->createJob('2026-09-02', charges: 100, minutes: 60);

        $this->service->createPayout($this->mower, [$paid->id], 80.0, 0.0, null, $this->admin);

        $this->assertSame([$unpaid->id], Job::query()->withoutMowerPayout()->pluck('id')->all());
        $this->assertSame([$paid->id], Job::query()->withMowerPayout()->pluck('id')->all());
    }

    public function test_payout_belongs_to_the_mower_and_exposes_its_jobs(): void
    {
        $job = $this->createJob('2026-10-01', charges: 100, minutes: 60);
        $payout = $this->service->createPayout($this->mower, [$job->id], 75.0, 5.0, null, $this->admin);

        $payout = MowerPayout::query()->with(['user', 'jobs'])->find($payout->id);

        $this->assertSame($this->mower->id, $payout->user->id);
        $this->assertSame([$job->id], $payout->jobs->pluck('id')->all());
        $this->assertSame(80.0, $payout->totalAmount());
        $this->assertSame(1, $this->mower->mowerPayouts()->count());
    }

    private function createJob(
        string $date,
        float $charges,
        int $minutes,
        string $status = 'Completed',
        ?User $doneBy = null
    ): Job {
        return Job::query()->create([
            'customer_name' => 'Payout Client',
            'phone' => '555-9000',
            'client_address' => '1 Payout Rd',
            'scheduled_date' => $date,
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'consumed_time_minutes' => $minutes,
            'required_services' => [ServiceTypes::all()[0] ?? 'Mowing'],
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
            'payment_mode' => 'Cash',
            'payment_status' => 'Received',
            'status' => $status,
            'charges' => $charges,
            'done_by_user_id' => ($doneBy ?? $this->mower)->id,
            'created_by' => $this->admin->id,
        ]);
    }
}
