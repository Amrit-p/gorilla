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

    public function test_customer_details_modal_renders_without_duplicate_contact_blocks(): void
    {
        $job = Job::query()->create([
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
        ]);

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

    public function test_job_show_page_no_longer_repeats_customer_contact_in_sidebar(): void
    {
        $job = Job::query()->create([
            'customer_name' => 'Show Client',
            'phone' => '555-3333',
            'email' => 'show.client@example.test',
            'client_address' => '12 Show St',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
            'estimated_duration_minutes' => 45,
            'required_services' => [ServiceTypes::all()[0] ?? 'Mowing'],
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
            'payment_mode' => 'Online',
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'status' => JobWorkflowStatus::COMPLETED->value,
            'charges' => 100,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.jobs.show', $job))
            ->assertOk()
            ->assertSee('Customer summary', false)
            ->assertSee('Open details', false)
            ->assertDontSee('>Name</dt>', false);
    }
}
