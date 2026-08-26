<?php

namespace Tests\Feature\Reports;

use App\Http\Middleware\EnsureMowerChecklistComplete;
use App\Models\EmployeeBonus;
use App\Models\Job;
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

    public function test_report_table_has_no_payout_column(): void
    {
        $this->createCompletedJobForMower();

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

        $this->assertStringNotContainsString('Payout', $html);
        $this->assertStringNotContainsString('mower-payout-input', $html);
        // The bonus column stays, so its $25.00 is still rendered.
        $this->assertStringContainsString('$25.00', $html);
    }

    public function test_excel_and_pdf_exports_render_without_the_payout_column(): void
    {
        $this->createCompletedJobForMower();

        $this->actingAs($this->manager)
            ->get(route('reports.mower.export'))
            ->assertOk();

        $this->actingAs($this->manager)
            ->get(route('reports.mower.export-pdf'))
            ->assertOk();
    }

    private function createCompletedJobForMower(): void
    {
        Job::query()->create([
            'customer_name' => 'Payout Client',
            'phone' => '555-9000',
            'client_address' => '1 Payout Rd',
            'scheduled_date' => now()->toDateString(),
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
