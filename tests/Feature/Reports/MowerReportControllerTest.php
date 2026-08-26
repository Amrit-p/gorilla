<?php

namespace Tests\Feature\Reports;

use App\Http\Middleware\EnsureMowerChecklistComplete;
use App\Models\EmployeeBonus;
use App\Models\Job;
use App\Models\MowerPayout;
use App\Models\User;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MowerReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->withoutMiddleware(EnsureMowerChecklistComplete::class);

        $this->manager = User::factory()->create(['is_active' => true]);
        $this->manager->assignRole(CrmRoles::OFFICE_MANAGER);

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);
    }

    public function test_office_manager_sees_dashboard_layout(): void
    {
        $this->actingAs($this->manager)
            ->get(route('reports.mower.index'))
            ->assertOk()
            ->assertSee('Mower Reports')
            ->assertSee('id="sidebar"', false)
            ->assertDontSee('mower-safe-bottom', false);
    }

    public function test_mower_sees_mower_layout(): void
    {
        $this->actingAs($this->mower)
            ->get(route('reports.mower.index'))
            ->assertOk()
            ->assertSee('Mower Report')
            ->assertSee('mower-safe-bottom', false)
            ->assertDontSee('id="sidebar"', false);
    }

    public function test_mower_dashboard_shows_report_link_for_permitted_user(): void
    {
        $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertOk()
            ->assertSee(route('reports.mower.index'), false);
    }

    public function test_mower_report_hides_advanced_filters_and_list_scope_for_mower(): void
    {
        $response = $this->actingAs($this->mower)
            ->get(route('reports.mower.index'))
            ->assertOk();

        $response->assertSee('name="search"', false);
        $response->assertSee('Scheduled Date');
        $response->assertDontSee('All zones');
        $response->assertDontSee('Any status');
        $response->assertDontSee('Completed but not verified');
    }

    public function test_mower_report_keeps_full_filters_but_hides_list_scope_for_office_manager(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('reports.mower.index'))
            ->assertOk();

        $response->assertSee('name="search"', false);
        $response->assertSee('All zones');
        $response->assertSee('Any status');
        $response->assertDontSee('Completed but not verified');
    }

    public function test_report_table_sums_bonus_and_payout_from_mower_payouts(): void
    {
        $job = $this->createCompletedJobForMower();
        $this->createPayoutForJob($job, amount: 90, bonus: 15);

        // Employee bonuses are no longer the source of the report's bonus column.
        EmployeeBonus::query()->create([
            'user_id' => $this->mower->id,
            'amount' => 25,
            'description' => 'Spot bonus',
            'bonus_date' => now()->toDateString(),
            'created_by' => $this->manager->id,
        ]);

        $html = $this->actingAs($this->manager)
            ->getJson(route('reports.mower.report'))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('Payout', $html);
        $this->assertStringContainsString('$90.00', $html);
        $this->assertStringContainsString('$15.00', $html);
        $this->assertStringNotContainsString('$25.00', $html);
    }

    public function test_report_table_ignores_payouts_for_jobs_outside_the_filtered_range(): void
    {
        $job = $this->createCompletedJobForMower(now()->subMonth()->toDateString());
        $this->createPayoutForJob($job, amount: 90, bonus: 15);
        $this->createCompletedJobForMower();

        $html = $this->actingAs($this->manager)
            ->getJson(route('reports.mower.report', [
                'date_range' => [
                    'start' => now()->toDateString(),
                    'end' => now()->toDateString(),
                ],
            ]))
            ->assertOk()
            ->json('html');

        $this->assertStringNotContainsString('$90.00', $html);
        $this->assertStringNotContainsString('$15.00', $html);
    }

    public function test_mower_does_not_see_the_payout_column(): void
    {
        if (CrmRoles::MOWER_SHOW_BONUS) {
            $this->markTestSkipped('Payout and bonus columns are visible to mowers by configuration.');
        }

        $job = $this->createCompletedJobForMower();
        $this->createPayoutForJob($job, amount: 90, bonus: 15);

        $html = $this->actingAs($this->mower)
            ->getJson(route('reports.mower.report'))
            ->assertOk()
            ->json('html');

        $this->assertStringNotContainsString('Payout', $html);
        $this->assertStringNotContainsString('$90.00', $html);
    }

    public function test_excel_and_pdf_exports_render_with_the_payout_column(): void
    {
        $job = $this->createCompletedJobForMower();
        $this->createPayoutForJob($job, amount: 90, bonus: 15);

        $this->actingAs($this->manager)
            ->get(route('reports.mower.export'))
            ->assertOk();

        $this->actingAs($this->manager)
            ->get(route('reports.mower.export-pdf'))
            ->assertOk();
    }

    public function test_payout_cells_open_the_payouts_modal(): void
    {
        $job = $this->createCompletedJobForMower();
        $this->createPayoutForJob($job, amount: 90, bonus: 15);

        $html = $this->actingAs($this->manager)
            ->getJson(route('reports.mower.report'))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('mower-payout-cell', $html);
    }

    public function test_payouts_endpoint_lists_the_payouts_behind_the_totals(): void
    {
        $job = $this->createCompletedJobForMower();
        $payout = $this->createPayoutForJob($job, amount: 90, bonus: 15, comment: 'April settlement');

        $response = $this->actingAs($this->manager)
            ->getJson(route('reports.mower.payouts', ['done_by_user_id' => $this->mower->id]))
            ->assertOk()
            ->assertJson(['count' => 1]);

        $html = $response->json('html');

        $this->assertStringContainsString('$90.00', $html);
        $this->assertStringContainsString('$15.00', $html);
        $this->assertStringContainsString('April settlement', $html);
        $this->assertStringContainsString('data-payout-id="'.$payout->id.'"', $html);
        // The manager can correct payouts, so the row actions are rendered.
        $this->assertStringContainsString('salary-payouts-edit', $html);
    }

    public function test_payouts_endpoint_only_lists_payouts_settling_the_filtered_jobs(): void
    {
        $oldJob = $this->createCompletedJobForMower(now()->subMonth()->toDateString());
        $this->createPayoutForJob($oldJob, amount: 90, bonus: 15);
        $recentJob = $this->createCompletedJobForMower();
        $this->createPayoutForJob($recentJob, amount: 40, bonus: 5);

        $html = $this->actingAs($this->manager)
            ->getJson(route('reports.mower.payouts', [
                'done_by_user_id' => $this->mower->id,
                'date_range' => [
                    'start' => now()->toDateString(),
                    'end' => now()->toDateString(),
                ],
            ]))
            ->assertOk()
            ->assertJson(['count' => 1])
            ->json('html');

        $this->assertStringContainsString('$40.00', $html);
        $this->assertStringNotContainsString('$90.00', $html);
    }

    public function test_payouts_list_is_read_only_without_the_salary_calculator_permission(): void
    {
        $salesManager = User::factory()->create(['is_active' => true]);
        $salesManager->assignRole(CrmRoles::SALES_MANAGER);

        $job = $this->createCompletedJobForMower();
        $this->createPayoutForJob($job, amount: 90, bonus: 15);

        $html = $this->actingAs($salesManager)
            ->getJson(route('reports.mower.payouts', ['done_by_user_id' => $this->mower->id]))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('$90.00', $html);
        $this->assertStringNotContainsString('salary-payouts-edit', $html);
        $this->assertStringNotContainsString('salary-payouts-delete', $html);
    }

    public function test_payouts_endpoint_is_closed_to_mowers_while_the_bonus_column_is_hidden(): void
    {
        if (CrmRoles::MOWER_SHOW_BONUS) {
            $this->markTestSkipped('Payout and bonus columns are visible to mowers by configuration.');
        }

        $job = $this->createCompletedJobForMower();
        $this->createPayoutForJob($job, amount: 90, bonus: 15);

        $this->actingAs($this->mower)
            ->getJson(route('reports.mower.payouts', ['done_by_user_id' => $this->mower->id]))
            ->assertForbidden();
    }

    private function createPayoutForJob(Job $job, float $amount, float $bonus, ?string $comment = null): MowerPayout
    {
        $payout = MowerPayout::query()->create([
            'user_id' => $this->mower->id,
            'amount' => $amount,
            'bonus' => $bonus,
            'comment' => $comment,
            'created_by' => $this->manager->id,
        ]);

        $payout->jobs()->attach($job->id);

        return $payout;
    }

    private function createCompletedJobForMower(?string $scheduledDate = null): Job
    {
        return Job::query()->create([
            'customer_name' => 'Payout Client',
            'phone' => '555-9000',
            'client_address' => '1 Payout Rd',
            'scheduled_date' => $scheduledDate ?? now()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0] ?? 'Mowing'],
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
            'payment_mode' => 'Cash',
            'payment_status' => 'Received',
            'status' => 'Completed',
            'charges' => 100,
            'incentive_percentage' => 10,
            'done_by_user_id' => $this->mower->id,
            'created_by' => $this->manager->id,
        ]);
    }
}
