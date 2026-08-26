<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\MowerPayout;
use App\Models\User;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryCalculatorControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole(CrmRoles::OFFICE_MANAGER);

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);
    }

    public function test_office_manager_sees_the_calculator_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.salary-calculator.index'))
            ->assertOk()
            ->assertSee('Salary Calculator')
            ->assertSee('salary-mower-select', false)
            ->assertSee('salary-year-select', false)
            // The months table and the job toolbar arrive over AJAX; the page ships the shell.
            ->assertSee('salary-months-table-container', false);
    }

    /**
     * The drill-down renders the full jobs table, so the page has to ship the
     * same modals and dropdown wiring the jobs list does - without them the row
     * actions and the customer-details popup are dead.
     */
    public function test_calculator_page_ships_the_job_modals_and_dropdown_wiring(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.salary-calculator.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="customer-details-modal"', $html);
        $this->assertStringContainsString('id="assign-job-modal"', $html);
        $this->assertStringContainsString('id="job-alert"', $html);
        $this->assertStringContainsString('function crmDropdown', $html);
        $this->assertStringContainsString("crmDropdown('.job-actions-btn'", $html);
        $this->assertStringContainsString('js-open-customer-details', $html);

        // crmDropdown must be defined before job-actions-script calls it.
        $this->assertLessThan(
            strpos($html, "crmDropdown('.job-actions-btn'"),
            strpos($html, 'function crmDropdown'),
        );
    }

    public function test_months_endpoint_returns_the_twelve_month_table(): void
    {
        $this->createJob('2026-03-10', charges: 250, minutes: 90);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.months', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
            ]))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $html = $response->json('html');

        $this->assertSame(12, substr_count($html, 'salary-month-cell'));
        $this->assertStringContainsString('Total Payout', $html);
        $this->assertStringContainsString('Total Hours', $html);
        $this->assertStringContainsString('$250.00', $html);
        // No Actions column on the main table any more.
        $this->assertStringNotContainsString('Generate Receipt', $html);
    }

    public function test_jobs_endpoint_returns_completed_jobs_for_that_month(): void
    {
        $inMonth = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $otherMonth = $this->createJob('2026-04-10', charges: 400, minutes: 60);

        $html = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.jobs', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
            ]))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('data-job-id="'.$inMonth->id.'"', $html);
        $this->assertStringNotContainsString('data-job-id="'.$otherMonth->id.'"', $html);
        $this->assertStringContainsString('job-select-checkbox', $html);
        // The drill-down ships the full jobs toolbar, including its job actions.
        $this->assertStringContainsString('job-bulk-toolbar', $html);
        $this->assertStringContainsString('job-bulk-payout', $html);
        $this->assertStringContainsString('job-bulk-assign', $html);
        $this->assertStringContainsString('job-payout-modal', $html);
        // It mounts under the shared container id so selection JS binds to it.
        $this->assertStringContainsString('id="jobs-table-container"', $html);
    }

    public function test_paid_jobs_stay_visible_and_are_stamped_instead_of_hidden(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);

        $this->payout([$job->id], 200);

        $html = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.jobs', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
            ]))
            ->assertOk()
            ->json('html');

        // The row is still listed, stamped, and locked against a second payout.
        $this->assertStringContainsString('data-job-id="'.$job->id.'"', $html);
        $this->assertStringContainsString('Mower paid', $html);
        $this->assertStringContainsString('data-mower-paid="true"', $html);
        $this->assertStringContainsString('disabled', $html);

        // The row itself must not be faded out any more. (The checkbox keeps a
        // disabled:opacity-50 utility, so this checks the <tr> class directly.)
        preg_match('/<tr[^>]*data-job-id="'.$job->id.'"[^>]*>/', $html, $rowTag);
        $this->assertNotEmpty($rowTag);
        $this->assertStringNotContainsString('opacity-50', $rowTag[0]);
    }

    public function test_paid_jobs_are_stamped_on_the_main_jobs_list_too(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $this->payout([$job->id], 200);

        $this->actingAs($this->admin)
            ->get(route('admin.jobs.index'))
            ->assertOk()
            ->assertSee('Mower paid');
    }

    public function test_months_table_renders_an_expandable_row_under_every_month(): void
    {
        $html = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.months', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
            ]))
            ->assertOk()
            ->json('html');

        // Each month owns the hidden row its job sub-table is loaded into.
        $this->assertSame(12, substr_count($html, 'salary-jobs-row'));
        $this->assertSame(12, substr_count($html, 'salary-jobs-slot'));
    }

    public function test_jobs_endpoint_paginates_the_drill_down(): void
    {
        foreach (range(1, 12) as $day) {
            $this->createJob(sprintf('2026-03-%02d', $day), charges: 100, minutes: 60);
        }

        $firstPage = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.jobs', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
            ]))
            ->assertOk()
            ->json('html');

        // Counted on the row checkbox markup: the class name also appears in the
        // partial's selection script, so a bare class count would over-report.
        $this->assertSame(10, substr_count($firstPage, 'aria-label="Select job"'));
        $this->assertStringContainsString('page=2', $firstPage);

        $secondPage = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.jobs', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
                'page' => 2,
            ]))
            ->assertOk()
            ->json('html');

        $this->assertSame(2, substr_count($secondPage, 'aria-label="Select job"'));
    }

    public function test_selection_endpoint_returns_one_group_for_a_single_mower(): void
    {
        $first = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $second = $this->createJob('2026-03-14', charges: 150, minutes: 30);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.selection'), [
                'job_ids' => [$first->id, $second->id],
            ])
            ->assertOk()
            ->assertJson(['ok' => true, 'job_count' => 2])
            ->assertJsonCount(1, 'groups');

        $group = $response->json('groups.0');

        $this->assertSame($this->mower->id, $group['mower_id']);
        $this->assertEquals(400.0, $group['total_sales']);
        $this->assertEquals(2.0, $group['total_hours']);
        $this->assertSame(2, $group['job_count']);
    }

    public function test_selection_endpoint_returns_one_group_per_mower(): void
    {
        $otherMower = User::factory()->create(['is_active' => true, 'name' => 'Zoe Mower']);
        $otherMower->assignRole(CrmRoles::MOWER);

        $mine = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $theirs = $this->createJob('2026-03-11', charges: 100, minutes: 60);
        $theirs->update(['done_by_user_id' => $otherMower->id]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.selection'), [
                'job_ids' => [$mine->id, $theirs->id],
            ])
            ->assertOk()
            ->assertJson(['ok' => true, 'job_count' => 2])
            ->assertJsonCount(2, 'groups');

        $byMower = collect($response->json('groups'))->keyBy('mower_id');

        $this->assertSame([$mine->id], $byMower[$this->mower->id]['job_ids']);
        $this->assertEquals(250.0, $byMower[$this->mower->id]['total_sales']);
        $this->assertSame([$theirs->id], $byMower[$otherMower->id]['job_ids']);
        $this->assertEquals(100.0, $byMower[$otherMower->id]['total_sales']);
    }

    public function test_store_records_one_payout_per_mower_group(): void
    {
        $otherMower = User::factory()->create(['is_active' => true]);
        $otherMower->assignRole(CrmRoles::MOWER);

        $mine = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $theirs = $this->createJob('2026-03-11', charges: 100, minutes: 60);
        $theirs->update(['done_by_user_id' => $otherMower->id]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [
                    ['mode' => 'create', 'mower_id' => $this->mower->id, 'job_ids' => [$mine->id], 'amount' => 200, 'bonus' => 10],
                    ['mode' => 'create', 'mower_id' => $otherMower->id, 'job_ids' => [$theirs->id], 'amount' => 80, 'bonus' => 0],
                ],
            ])
            ->assertOk()
            ->assertJson(['created' => 2, 'updated' => 0]);

        $this->assertDatabaseCount('mower_payouts', 2);
        $this->assertDatabaseHas('mower_payouts', ['user_id' => $this->mower->id, 'amount' => 200, 'bonus' => 10]);
        $this->assertDatabaseHas('mower_payouts', ['user_id' => $otherMower->id, 'amount' => 80, 'bonus' => 0]);
    }

    public function test_store_rolls_back_every_group_when_one_is_invalid(): void
    {
        $otherMower = User::factory()->create(['is_active' => true]);
        $otherMower->assignRole(CrmRoles::MOWER);

        $good = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $alreadyPaid = $this->createJob('2026-03-11', charges: 100, minutes: 60);
        $alreadyPaid->update(['done_by_user_id' => $otherMower->id]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [['mode' => 'create', 'mower_id' => $otherMower->id, 'job_ids' => [$alreadyPaid->id], 'amount' => 50, 'bonus' => 0]],
            ])
            ->assertOk();

        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [
                    ['mode' => 'create', 'mower_id' => $this->mower->id, 'job_ids' => [$good->id], 'amount' => 200, 'bonus' => 0],
                    ['mode' => 'create', 'mower_id' => $otherMower->id, 'job_ids' => [$alreadyPaid->id], 'amount' => 50, 'bonus' => 0],
                ],
            ])
            ->assertStatus(422);

        // The valid first group must not have been committed.
        $this->assertDatabaseCount('mower_payouts', 1);
        $this->assertFalse($good->fresh()->hasMowerPayout());
    }

    public function test_selection_endpoint_refuses_jobs_that_are_not_completed(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $job->update(['status' => 'Pending']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.selection'), [
                'job_ids' => [$job->id],
            ])
            ->assertOk()
            ->assertJson(['ok' => false]);
    }

    public function test_store_derives_the_mower_when_none_is_supplied(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);

        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [['mode' => 'create', 'job_ids' => [$job->id], 'amount' => 200, 'bonus' => 0]],
            ])
            ->assertOk();

        $this->assertDatabaseHas('mower_payouts', [
            'user_id' => $this->mower->id,
            'amount' => 200,
        ]);
    }

    public function test_store_creates_a_payout_and_links_the_selected_jobs(): void
    {
        $first = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $second = $this->createJob('2026-03-14', charges: 150, minutes: 30);

        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [[
                    'mode' => 'create',
                    'mower_id' => $this->mower->id,
                    'job_ids' => [$first->id, $second->id],
                    'amount' => 320.75,
                    'bonus' => 25,
                    'comment' => 'March run',
                ]],
            ])
            ->assertOk()
            ->assertJson(['created' => 1, 'updated' => 0]);

        $this->assertDatabaseHas('mower_payouts', [
            'user_id' => $this->mower->id,
            'amount' => 320.75,
            'bonus' => 25,
            'comment' => 'March run',
        ]);
        $this->assertDatabaseCount('mower_payout_job', 2);
    }

    public function test_store_still_refuses_to_create_a_second_payout_for_a_paid_job(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);

        $this->payout([$job->id], 200);

        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [['mode' => 'create', 'mower_id' => $this->mower->id, 'job_ids' => [$job->id], 'amount' => 200, 'bonus' => 0]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('job_ids');

        $this->assertDatabaseCount('mower_payouts', 1);
    }

    public function test_selection_offers_an_edit_card_for_a_job_that_is_already_paid(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $payoutId = $this->payout([$job->id], 200, 15, 'First run');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.selection'), ['job_ids' => [$job->id]])
            ->assertOk()
            ->assertJson(['ok' => true])
            ->assertJsonCount(1, 'groups');

        $card = $response->json('groups.0');

        $this->assertSame('update', $card['mode']);
        $this->assertSame($payoutId, $card['payout_id']);
        $this->assertEquals(200.0, $card['amount']);
        $this->assertEquals(15.0, $card['bonus']);
        $this->assertSame('First run', $card['comment']);
    }

    public function test_selection_mixes_create_and_edit_cards(): void
    {
        $paid = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $payoutId = $this->payout([$paid->id], 200);
        $unpaid = $this->createJob('2026-03-14', charges: 150, minutes: 30);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.selection'), [
                'job_ids' => [$paid->id, $unpaid->id],
            ])
            ->assertOk()
            ->assertJson(['ok' => true])
            ->assertJsonCount(2, 'groups');

        $byMode = collect($response->json('groups'))->keyBy('mode');

        $this->assertSame([$unpaid->id], $byMode['create']['job_ids']);
        $this->assertEquals(150.0, $byMode['create']['amount']);
        $this->assertSame($payoutId, $byMode['update']['payout_id']);
    }

    public function test_store_creates_and_updates_in_one_call(): void
    {
        $paid = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $payoutId = $this->payout([$paid->id], 200);
        $unpaid = $this->createJob('2026-03-14', charges: 150, minutes: 30);

        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [
                    ['mode' => 'create', 'mower_id' => $this->mower->id, 'job_ids' => [$unpaid->id], 'amount' => 150, 'bonus' => 5],
                    ['mode' => 'update', 'payout_id' => $payoutId, 'amount' => 260, 'bonus' => 20, 'comment' => 'Corrected'],
                ],
            ])
            ->assertOk()
            ->assertJson(['created' => 1, 'updated' => 1]);

        $this->assertDatabaseCount('mower_payouts', 2);
        $this->assertDatabaseHas('mower_payouts', ['id' => $payoutId, 'amount' => 260, 'bonus' => 20, 'comment' => 'Corrected']);
        $this->assertTrue($unpaid->fresh()->hasMowerPayout());
    }

    public function test_paid_jobs_remain_selectable_so_their_payout_can_be_edited(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $this->payout([$job->id], 200);

        $html = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.jobs', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
            ]))
            ->assertOk()
            ->json('html');

        preg_match('/<input[^>]*job-select-checkbox[^>]*data-job-id="'.$job->id.'"[^>]*>/', $html, $box);
        $this->assertNotEmpty($box);
        $this->assertStringNotContainsString('disabled', $box[0]);
    }

    public function test_store_requires_at_least_one_job(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [['mode' => 'create', 'mower_id' => $this->mower->id, 'job_ids' => [], 'amount' => 200, 'bonus' => 0]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('payouts.0.job_ids');
    }

    public function test_months_table_shows_a_clickable_payout_count_column(): void
    {
        $march = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $this->payout([$march->id], 200);

        $html = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.months', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
            ]))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('>Payouts</th>', $html);
        // Only March has a payout, and both its count pill and its total-payout
        // amount open the modal, so exactly two clickable cells are rendered.
        $this->assertSame(2, substr_count($html, 'salary-payouts-cell'));
        $this->assertStringContainsString('data-month="3"', $html);
        $this->assertStringContainsString('data-year="2026"', $html);
    }

    public function test_month_payouts_endpoint_lists_payouts_with_creator_and_actions(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $payoutId = $this->payout([$job->id], 200, 15, 'March run');

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.month-payouts', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
            ]))
            ->assertOk()
            ->assertJson(['count' => 1, 'month_label' => 'March 2026']);

        $html = $response->json('html');

        // Creator, the comment, and both row actions are present.
        $this->assertStringContainsString($this->admin->name, $html);
        $this->assertStringContainsString('March run', $html);
        $this->assertStringContainsString('salary-payouts-edit', $html);
        $this->assertStringContainsString('salary-payouts-delete', $html);
        // The job count drills down into the payout's jobs.
        $this->assertStringContainsString('salary-payout-jobs-cell', $html);
        $this->assertStringContainsString('data-payout-id="'.$payoutId.'"', $html);
        // The edit button carries the values its inline form is pre-filled from.
        $this->assertStringContainsString('data-amount="200.00"', $html);
        $this->assertStringContainsString('data-bonus="15.00"', $html);
    }

    public function test_month_payouts_endpoint_only_returns_that_months_payouts(): void
    {
        $march = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $marchPayout = $this->payout([$march->id], 200);

        $april = $this->createJob('2026-04-10', charges: 400, minutes: 60);
        $aprilPayout = $this->payout([$april->id], 300);

        $html = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.month-payouts', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
            ]))
            ->assertOk()
            ->assertJson(['count' => 1])
            ->json('html');

        $this->assertStringContainsString('data-payout-id="'.$marchPayout.'"', $html);
        $this->assertStringNotContainsString('data-payout-id="'.$aprilPayout.'"', $html);
    }

    public function test_month_payouts_endpoint_reports_an_empty_month(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.month-payouts', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
            ]))
            ->assertOk()
            ->assertJson(['count' => 0])
            ->assertSee('No payouts recorded', false);
    }

    public function test_mower_cannot_list_month_payouts(): void
    {
        $this->actingAs($this->mower)
            ->getJson(route('admin.salary-calculator.month-payouts', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
            ]))
            ->assertForbidden();
    }

    public function test_payout_jobs_endpoint_lists_the_jobs_a_payout_settles(): void
    {
        $first = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $second = $this->createJob('2026-03-14', charges: 150, minutes: 30);
        $other = $this->createJob('2026-03-20', charges: 999, minutes: 60);
        $payoutId = $this->payout([$first->id, $second->id], 380);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.payouts.jobs', $payoutId))
            ->assertOk()
            ->assertJson(['count' => 2]);

        $html = $response->json('html');

        // Both settled jobs are listed, but a job on another payout is not.
        $this->assertStringContainsString('10 Mar 2026', $html);
        $this->assertStringContainsString('14 Mar 2026', $html);
        $this->assertStringNotContainsString('20 Mar 2026', $html);
        // The footer totals the charges of the two settled jobs.
        $this->assertStringContainsString('$400.00', $html);
    }

    public function test_mower_cannot_view_payout_jobs(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $payoutId = $this->payout([$job->id], 200);

        $this->actingAs($this->mower)
            ->getJson(route('admin.salary-calculator.payouts.jobs', $payoutId))
            ->assertForbidden();
    }

    public function test_deleting_a_payout_releases_its_jobs(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $payoutId = $this->payout([$job->id], 200);

        $this->actingAs($this->admin)
            ->deleteJson(route('admin.salary-calculator.payouts.destroy', $payoutId))
            ->assertOk();

        $this->assertDatabaseMissing('mower_payouts', ['id' => $payoutId]);
        $this->assertDatabaseCount('mower_payout_job', 0);
        $this->assertFalse($job->fresh()->hasMowerPayout());

        // Released, so it can be paid out again.
        $this->payout([$job->id], 180);
        $this->assertTrue($job->fresh()->hasMowerPayout());
    }

    public function test_paid_stamp_links_to_the_payout_for_editing(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $payoutId = $this->payout([$job->id], 200);

        $html = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.jobs', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 3,
            ]))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('js-open-payout', $html);
        $this->assertStringContainsString('data-payout-id="'.$payoutId.'"', $html);
        $this->assertStringContainsString('data-job-id="'.$job->id.'"', $html);
    }

    public function test_mower_cannot_edit_or_delete_a_payout(): void
    {
        $job = $this->createJob('2026-03-10', charges: 250, minutes: 90);
        $payoutId = $this->payout([$job->id], 200);

        $this->actingAs($this->mower)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [['mode' => 'update', 'payout_id' => $payoutId, 'amount' => 1, 'bonus' => 0]],
            ])
            ->assertForbidden();

        $this->actingAs($this->mower)
            ->deleteJson(route('admin.salary-calculator.payouts.destroy', $payoutId))
            ->assertForbidden();

        $this->assertDatabaseHas('mower_payouts', ['id' => $payoutId, 'amount' => 200]);
    }

    public function test_mower_cannot_reach_the_salary_calculator(): void
    {
        $this->actingAs($this->mower)
            ->get(route('admin.salary-calculator.index'))
            ->assertForbidden();

        $this->actingAs($this->mower)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [['mode' => 'create', 'mower_id' => $this->mower->id, 'job_ids' => [1], 'amount' => 10, 'bonus' => 0]],
            ])
            ->assertForbidden();
    }

    /**
     * @param  array<int, int>  $jobIds
     * @return int the new payout id
     */
    private function payout(array $jobIds, float $amount, float $bonus = 0, ?string $comment = null): int
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'payouts' => [[
                    'mode' => 'create',
                    'job_ids' => $jobIds,
                    'amount' => $amount,
                    'bonus' => $bonus,
                    'comment' => $comment,
                ]],
            ])
            ->assertOk();

        return (int) MowerPayout::query()->latest('id')->value('id');
    }

    private function createJob(string $date, float $charges, int $minutes): Job
    {
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
            'status' => 'Completed',
            'charges' => $charges,
            'done_by_user_id' => $this->mower->id,
            'created_by' => $this->admin->id,
        ]);
    }
}
