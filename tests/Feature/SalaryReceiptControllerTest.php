<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureMowerChecklistComplete;
use App\Models\SalaryReceipt;
use App\Models\User;
use App\Services\SalaryCalculatorService;
use App\Support\CrmRoles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryReceiptControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mower;

    private User $otherMower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->withoutMiddleware(EnsureMowerChecklistComplete::class);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole(CrmRoles::OFFICE_MANAGER);

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);

        $this->otherMower = User::factory()->create(['is_active' => true]);
        $this->otherMower->assignRole(CrmRoles::MOWER);
    }

    private function createReceipt(User $mower): SalaryReceipt
    {
        return SalaryReceipt::create([
            'mower_id' => $mower->id,
            'period_type' => 'month',
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'total_sales' => 1000,
            'total_bonus' => 0,
            'percentage_used' => 20,
            'formula_snapshot' => SalaryCalculatorService::defaultFormulaRule(),
            'salary_amount' => 200,
            'generated_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_view_receipts_index(): void
    {
        $this->createReceipt($this->mower);

        $this->actingAs($this->admin)
            ->get(route('admin.salary-receipts.index'))
            ->assertOk()
            ->assertSee($this->mower->name);
    }

    public function test_mower_only_sees_own_receipt_in_index(): void
    {
        $this->createReceipt($this->mower);
        $this->createReceipt($this->otherMower);

        $response = $this->actingAs($this->mower)
            ->get(route('admin.salary-receipts.index'))
            ->assertOk();

        $response->assertViewHas('receipts', fn ($receipts) => $receipts->total() === 1);
    }

    public function test_mower_can_view_own_receipt(): void
    {
        $receipt = $this->createReceipt($this->mower);

        $this->actingAs($this->mower)
            ->get(route('admin.salary-receipts.show', $receipt))
            ->assertOk();
    }

    public function test_mower_cannot_view_another_mowers_receipt(): void
    {
        $receipt = $this->createReceipt($this->otherMower);

        $this->actingAs($this->mower)
            ->get(route('admin.salary-receipts.show', $receipt))
            ->assertForbidden();
    }

    public function test_mower_cannot_access_calculator_page(): void
    {
        $this->actingAs($this->mower)->get(route('admin.salary-calculator.index'))->assertForbidden();
    }

    public function test_admin_can_access_calculator_page(): void
    {
        $this->actingAs($this->admin)->get(route('admin.salary-calculator.index'))->assertOk();
    }

    public function test_admin_can_delete_receipt(): void
    {
        $receipt = $this->createReceipt($this->mower);

        $this->actingAs($this->admin)
            ->delete(route('admin.salary-receipts.destroy', $receipt))
            ->assertRedirect(route('admin.salary-receipts.index'));

        $this->assertDatabaseMissing('salary_receipts', ['id' => $receipt->id]);
    }

    public function test_mower_cannot_delete_receipt(): void
    {
        $receipt = $this->createReceipt($this->mower);

        $this->actingAs($this->mower)
            ->delete(route('admin.salary-receipts.destroy', $receipt))
            ->assertForbidden();

        $this->assertDatabaseHas('salary_receipts', ['id' => $receipt->id]);
    }
}
