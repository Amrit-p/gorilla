<?php

namespace Tests\Feature;

use App\Contracts\Reports\MowerReportInterface;
use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\Models\User;
use App\Notifications\SalaryReceiptGeneratedNotification;
use App\Services\SalaryCalculatorService;
use App\Support\CrmRoles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SalaryCalculatorServiceTest extends TestCase
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

    private function bindFakeReport(float $totalSales, float $totalBonus): void
    {
        $this->app->instance(MowerReportInterface::class, new class($totalSales, $totalBonus) implements MowerReportInterface
        {
            public function __construct(private float $totalSales, private float $totalBonus) {}

            public function generate(MowerRequestReportDTO $request): Collection
            {
                return collect([[
                    'user_id' => (int) $request->user_id,
                    'total_sales' => $this->totalSales,
                    'bonus' => $this->totalBonus,
                ]]);
            }

            public function export(MowerRequestReportDTO $request): Response
            {
                throw new \RuntimeException('not used in tests');
            }
        });
    }

    public function test_fetch_totals_ignores_rows_belonging_to_other_users(): void
    {
        $otherMower = User::factory()->create(['is_active' => true]);
        $otherMower->assignRole(CrmRoles::MOWER);

        // Simulate MowerReportService returning a row for a *different* user
        // (e.g. because that user is the job's done_by_user_id while our mower
        // is only an assigned collaborator on the same job) ahead of our mower's
        // own row, to prove we don't just blindly take the first result.
        $this->app->instance(MowerReportInterface::class, new class($otherMower->id) implements MowerReportInterface
        {
            public function __construct(private int $otherMowerId) {}

            public function generate(MowerRequestReportDTO $request): Collection
            {
                return collect([
                    ['user_id' => $this->otherMowerId, 'total_sales' => 9999.0, 'bonus' => 9999.0],
                    ['user_id' => (int) $request->user_id, 'total_sales' => 500.0, 'bonus' => 25.0],
                ]);
            }

            public function export(MowerRequestReportDTO $request): Response
            {
                throw new \RuntimeException('not used in tests');
            }
        });

        $service = $this->app->make(SalaryCalculatorService::class);
        $rows = $service->monthlyBreakdown($this->mower, 2026);

        $this->assertSame(500.0, $rows[0]->total_sales);
        $this->assertSame(25.0, $rows[0]->total_bonus);
    }

    public function test_monthly_breakdown_defaults_unset_months_to_default_percentage(): void
    {
        $this->bindFakeReport(1000.0, 50.0);

        $service = $this->app->make(SalaryCalculatorService::class);
        $rows = $service->monthlyBreakdown($this->mower, 2026);

        $this->assertCount(12, $rows);
        $this->assertSame('January', $rows[0]->month_label);
        $this->assertSame(1000.0, $rows[0]->total_sales);
        $this->assertSame(50.0, $rows[0]->total_bonus);
        $this->assertSame(20.0, $rows[0]->percentage);
        $this->assertSame(250.0, $rows[0]->salary_amount);
        $this->assertNull($rows[0]->receipt_id);
    }

    public function test_generate_creates_receipt_and_notifies_mower(): void
    {
        Notification::fake();
        $this->bindFakeReport(2000.0, 100.0);

        $service = $this->app->make(SalaryCalculatorService::class);
        $receipt = $service->generate($this->mower, 2026, 7, 25.0, $this->admin);

        $this->assertDatabaseHas('salary_receipts', [
            'id' => $receipt->id,
            'mower_id' => $this->mower->id,
            'total_sales' => '2000.00',
            'total_bonus' => '100.00',
            'percentage_used' => '25.00',
            'salary_amount' => '600.00',
        ]);

        Notification::assertSentTo($this->mower, SalaryReceiptGeneratedNotification::class);
    }

    public function test_monthly_breakdown_reflects_existing_receipt_for_that_month(): void
    {
        $this->bindFakeReport(2000.0, 100.0);

        $service = $this->app->make(SalaryCalculatorService::class);
        $service->generate($this->mower, 2026, 7, 25.0, $this->admin);

        // Change the underlying totals to prove the receipt month is a frozen snapshot, not recomputed.
        $this->bindFakeReport(9999.0, 9999.0);
        $service = $this->app->make(SalaryCalculatorService::class);

        $rows = $service->monthlyBreakdown($this->mower, 2026);
        $julyRow = $rows[6];

        $this->assertNotNull($julyRow->receipt_id);
        $this->assertSame(2000.0, $julyRow->total_sales);
        $this->assertSame(25.0, $julyRow->percentage);
        $this->assertSame(600.0, $julyRow->salary_amount);
    }

    public function test_generate_rejects_duplicate_receipt_for_same_month(): void
    {
        $this->bindFakeReport(500.0, 0.0);

        $service = $this->app->make(SalaryCalculatorService::class);
        $service->generate($this->mower, 2026, 7, 20.0, $this->admin);

        $this->expectException(ValidationException::class);
        $service->generate($this->mower, 2026, 7, 20.0, $this->admin);
    }

    public function test_delete_receipt_allows_regeneration_for_same_month(): void
    {
        $this->bindFakeReport(500.0, 0.0);

        $service = $this->app->make(SalaryCalculatorService::class);
        $receipt = $service->generate($this->mower, 2026, 7, 20.0, $this->admin);
        $service->deleteReceipt($receipt);

        $this->assertDatabaseMissing('salary_receipts', ['id' => $receipt->id]);

        $newReceipt = $service->generate($this->mower, 2026, 7, 20.0, $this->admin);

        $this->assertNotSame($receipt->id, $newReceipt->id);
        $this->assertDatabaseHas('salary_receipts', ['id' => $newReceipt->id]);
    }
}
