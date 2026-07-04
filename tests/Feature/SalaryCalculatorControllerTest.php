<?php

namespace Tests\Feature;

use App\Contracts\Reports\MowerReportInterface;
use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\Models\User;
use App\Support\CrmRoles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
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

        $this->app->instance(MowerReportInterface::class, new class implements MowerReportInterface
        {
            public function generate(MowerRequestReportDTO $request): Collection
            {
                return collect([['user_id' => (int) $request->user_id, 'total_sales' => 800.0, 'bonus' => 20.0]]);
            }

            public function export(MowerRequestReportDTO $request): Response
            {
                throw new \RuntimeException('not used in tests');
            }
        });
    }

    public function test_months_endpoint_returns_twelve_rows_for_mower(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.salary-calculator.months', [
                'mower_id' => $this->mower->id,
                'year' => 2026,
            ]))
            ->assertOk();

        $response->assertJsonStructure(['html']);
        $this->assertSame(12, substr_count($response->json('html'), 'Generate Receipt'));
    }

    public function test_store_generates_receipt_via_ajax(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.salary-calculator.store'), [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 7,
                'percentage' => 15,
            ])
            ->assertOk();

        $response->assertJsonStructure(['receipt_id', 'receipt_url', 'salary_amount']);
        $this->assertEquals(140.0, $response->json('salary_amount'));

        $this->assertDatabaseHas('salary_receipts', [
            'mower_id' => $this->mower->id,
            'percentage_used' => '15.00',
        ]);
    }

    public function test_mower_cannot_generate_receipt(): void
    {
        $this->actingAs($this->mower)
            ->postJson(route('admin.salary-calculator.store'), [
                'mower_id' => $this->mower->id,
                'year' => 2026,
                'month' => 7,
                'percentage' => 15,
            ])
            ->assertForbidden();
    }
}
