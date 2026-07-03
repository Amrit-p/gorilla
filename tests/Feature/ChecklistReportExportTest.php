<?php

namespace Tests\Feature;

use App\Models\Checklist;
use App\Models\ChecklistPoint;
use App\Models\User;
use App\Support\CrmRoles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChecklistReportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private User $mower;

    private Checklist $checklist;

    private ChecklistPoint $point;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->manager = User::factory()->create(['is_active' => true]);
        $this->manager->assignRole(CrmRoles::OFFICE_MANAGER);

        $this->mower = User::factory()->create(['name' => 'Alice', 'is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);

        $this->checklist = Checklist::create(['name' => 'Daily Safety']);
        $this->point = ChecklistPoint::create([
            'checklist_id' => $this->checklist->id,
            'heading' => 'Helmet',
            'text' => 'Wear a helmet.',
            'sort_order' => 0,
        ]);

        DB::table('mower_checklist_submissions')->insert([
            'user_id' => $this->mower->id,
            'checklist_point_id' => $this->point->id,
            'date' => '2024-01-15',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    public function test_excel_export_returns_xlsx_download(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('reports.checklist.export', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31',
            ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('checklist_report_', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_pdf_export_returns_pdf_download(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('reports.checklist.export-pdf', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31',
            ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('checklist_report_', $response->headers->get('Content-Disposition'));
    }

    public function test_excel_export_without_date_filter_returns_xlsx(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('reports.checklist.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_pdf_export_without_date_filter_returns_pdf(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('reports.checklist.export-pdf'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_index_page_contains_export_buttons(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('reports.checklist.index'));

        $response->assertOk();
        $response->assertSee('checklist-excel-btn');
        $response->assertSee('checklist-pdf-btn');
    }

    public function test_mower_report_only_shows_own_submissions(): void
    {
        $otherMower = User::factory()->create(['name' => 'Bob', 'is_active' => true]);
        $otherMower->assignRole(CrmRoles::MOWER);

        DB::table('mower_checklist_submissions')->insert([
            'user_id' => $otherMower->id,
            'checklist_point_id' => $this->point->id,
            'date' => '2024-01-16',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $response = $this->actingAs($this->mower)
            ->get(route('reports.checklist.report', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31',
                'user_id' => $otherMower->id,
            ]));

        $response->assertOk();
        $response->assertSee('Alice');
        $response->assertDontSee('Bob');
    }

    public function test_mower_index_page_hides_employee_and_checklist_filters(): void
    {
        $response = $this->actingAs($this->mower)
            ->get(route('reports.checklist.index'));

        $response->assertOk();
        $response->assertDontSee('All Employees');
        $response->assertDontSee('All Checklists');
    }

    public function test_manager_index_page_shows_employee_and_checklist_filters(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('reports.checklist.index'));

        $response->assertOk();
        $response->assertSee('All Employees');
        $response->assertSee('All Checklists');
    }
}
