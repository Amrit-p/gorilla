<?php

namespace Tests\Feature;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\User;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDetailsModalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeJob(array $overrides = []): Job
    {
        return Job::query()->create(array_merge([
            'customer_name' => 'Modal Client',
            'phone' => '555-4444',
            'email' => 'modal.client@example.test',
            'client_address' => '99 Popup Ave',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0] ?? 'Mowing'],
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
            'payment_mode' => 'Cash',
            'payment_status' => JobOperationalPaymentStatus::PENDING->value,
            'status' => JobWorkflowStatus::PENDING->value,
            'charges' => 80,
            'created_by' => $this->admin->id,
        ], $overrides));
    }

    public function test_customer_details_modal_renders_without_duplicate_contact_blocks(): void
    {
        $job = $this->makeJob();

        $html = $this->actingAs($this->admin)
            ->get(route('admin.jobs.customer-details', $job))
            ->assertOk()
            ->assertSee('Customer Details', false)
            ->assertSee('99 Popup Ave', false)
            ->assertSee('555-4444', false)
            ->assertSee('Total revenue', false)
            ->assertSee('Payment received', false)
            ->assertSee('Balance due', false)
            ->assertSee('Edit profile', false)
            ->getContent();

        // Contact fields appear once in the profile column — not duplicated as a second "Name/Phone/Email" block.
        $this->assertSame(1, substr_count($html, '555-4444'));
        $this->assertSame(1, substr_count($html, 'modal.client@example.test'));
    }

    public function test_modal_lists_every_job_grouped_by_address_with_its_own_payment_detail(): void
    {
        $current = $this->makeJob([
            'scheduled_date' => now()->toDateString(),
            'charges' => 80,
        ]);

        // Same address, different contact details — still part of the address group.
        $paid = $this->makeJob([
            'phone' => '555-9999',
            'email' => 'someone.else@example.test',
            'client_address' => '99 popup ave  ',
            'scheduled_date' => now()->subWeek()->toDateString(),
            'payment_mode' => 'Online',
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'status' => JobWorkflowStatus::COMPLETED->value,
            'charges' => 120,
        ]);

        $unpaid = $this->makeJob([
            'scheduled_date' => now()->subMonth()->toDateString(),
            'charges' => 50,
        ]);

        $otherAddress = $this->makeJob([
            'client_address' => '7 Elsewhere Rd',
            'charges' => 999,
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.jobs.customer-details', $current))
            ->assertOk()
            ->assertSee('Jobs at this address', false)
            ->assertSee('3 jobs', false)
            ->getContent();

        $this->assertStringContainsString('data-address-job-id="'.$current->id.'"', $html);
        $this->assertStringContainsString('data-address-job-id="'.$paid->id.'"', $html);
        $this->assertStringContainsString('data-address-job-id="'.$unpaid->id.'"', $html);
        $this->assertStringNotContainsString('data-address-job-id="'.$otherAddress->id.'"', $html);

        // Per-job payment detail: amount, pay mode and paid/pending state.
        $this->assertStringContainsString('$120.00', $html);
        $this->assertStringContainsString('Online', $html);
        $this->assertStringContainsString('Paid', $html);

        // Totals cover the whole address group, not just the clicked job.
        $this->assertStringContainsString('$250.00', $html);
        $this->assertStringContainsString('$130.00', $html);
        $this->assertStringNotContainsString('$999.00', $html);
    }

    public function test_admin_gets_verify_and_delete_controls_for_each_job_in_the_group(): void
    {
        $current = $this->makeJob(['status' => JobWorkflowStatus::COMPLETED->value]);
        $verified = $this->makeJob([
            'scheduled_date' => now()->subWeek()->toDateString(),
            'status' => JobWorkflowStatus::COMPLETED->value,
            'verified_at' => now(),
            'verified_by' => $this->admin->id,
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.jobs.customer-details', $current))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/customer-details-verify[^>]*data-id="'.$current->id.'"/s',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/customer-details-delete[^>]*data-id="'.$current->id.'"/s',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/customer-details-delete[^>]*data-id="'.$verified->id.'"/s',
            $html
        );

        // An already-verified job keeps its delete control but loses the verify button.
        $this->assertDoesNotMatchRegularExpression(
            '/customer-details-verify[^>]*data-id="'.$verified->id.'"/s',
            $html
        );
    }

    public function test_customer_details_modal_shell_does_not_block_delegated_click_handlers(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.jobs.index'))
            ->assertOk()
            ->getContent();

        $shell = strstr($html, 'id="customer-details-modal"');
        $this->assertIsString($shell, 'Customer details modal shell is missing from the page.');

        // The verify/delete/close controls are wired with document-level delegation,
        // so no wrapper inside the modal may stop clicks from bubbling to document.
        $this->assertStringNotContainsString(
            'event.stopPropagation()',
            substr($shell, 0, 600)
        );
    }

    public function test_verify_button_is_withheld_from_jobs_that_are_not_completed(): void
    {
        $pending = $this->makeJob(['status' => JobWorkflowStatus::PENDING->value]);
        $completed = $this->makeJob([
            'scheduled_date' => now()->subWeek()->toDateString(),
            'status' => JobWorkflowStatus::COMPLETED->value,
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.jobs.customer-details', $pending))
            ->assertOk()
            ->getContent();

        // The bulk-verify endpoint rejects anything that is not Completed, so the
        // modal must not offer a clickable Verify control for a Pending job.
        $this->assertDoesNotMatchRegularExpression(
            '/customer-details-verify[^>]*data-id="'.$pending->id.'"/s',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/customer-details-verify[^>]*data-id="'.$completed->id.'"/s',
            $html
        );

        // Only the Completed job is offered to the bulk action.
        $this->assertMatchesRegularExpression(
            '/customer-details-verify-all[^>]*data-ids="'.$completed->id.'"/s',
            $html
        );
    }

    public function test_verified_jobs_get_an_unverify_control_and_can_be_unverified(): void
    {
        $verified = $this->makeJob([
            'status' => JobWorkflowStatus::COMPLETED->value,
            'verified_at' => now(),
            'verified_by' => $this->admin->id,
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.jobs.customer-details', $verified))
            ->assertOk()
            ->getContent();

        // A verified job swaps its Verify control for Unverify.
        $this->assertMatchesRegularExpression(
            '/customer-details-unverify[^>]*data-id="'.$verified->id.'"/s',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/customer-details-verify[^>]*data-id="'.$verified->id.'"/s',
            $html
        );

        // Unverifying has no status precondition, unlike verifying.
        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$verified->id],
                'verified' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Job verification removed.');

        $this->assertNull($verified->fresh()->verified_at);
    }

    public function test_bulk_verify_rejects_a_pending_job(): void
    {
        $pending = $this->makeJob(['status' => JobWorkflowStatus::PENDING->value]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$pending->id],
                'verified' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'None of the selected jobs can be verified. Jobs must be Completed.');

        $this->assertNull($pending->fresh()->verified_at);
    }

    public function test_job_show_page_no_longer_repeats_customer_contact_in_sidebar(): void
    {
        $job = $this->makeJob([
            'customer_name' => 'Show Client',
            'phone' => '555-3333',
            'email' => 'show.client@example.test',
            'client_address' => '12 Show St',
            'scheduled_time' => '10:00',
            'estimated_duration_minutes' => 45,
            'payment_mode' => 'Online',
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'status' => JobWorkflowStatus::COMPLETED->value,
            'charges' => 100,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.jobs.show', $job))
            ->assertOk()
            ->assertSee('Customer summary', false)
            ->assertSee('Open details', false)
            ->assertDontSee('>Name</dt>', false);
    }
}
