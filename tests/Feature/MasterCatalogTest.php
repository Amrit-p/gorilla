<?php

namespace Tests\Feature;

use App\Models\EquipmentType;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\MasterCatalog;
use App\Support\ServiceTypes;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterCatalogTest extends TestCase
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

    public function test_service_type_crud_and_cache(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.masters.service-types.index'))
            ->assertOk()
            ->assertSee('Mulching');

        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.service-types.store'), [
                'name' => 'Custom Service',
                'sort_order' => 10,
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertContains('Custom Service', ServiceTypes::all());

        $record = ServiceType::query()->where('name', 'Custom Service')->firstOrFail();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.service-types.update', $record), [
                'name' => 'Custom Service Updated',
                'sort_order' => 11,
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('service_types', ['name' => 'Custom Service Updated']);
    }

    public function test_duplicate_service_type_name_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.service-types.store'), [
                'name' => 'Mulching',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_equipment_type_requires_valid_color_code(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.equipment-types.store'), [
                'name' => 'Test Mower',
                'color_code' => 'not-a-color',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['color_code']);
    }

    public function test_equipment_type_crud_with_color(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.equipment-types.store'), [
                'name' => 'Blue Mower',
                'color_code' => '#0000ff',
                'sort_order' => 5,
                'is_active' => true,
            ])
            ->assertOk();

        $record = EquipmentType::query()->where('name', 'Blue Mower')->firstOrFail();
        $this->assertSame('#0000ff', $record->color_code);
    }

    public function test_status_toggle_deactivates_record(): void
    {
        $record = ServiceType::query()->where('name', 'Mulching')->firstOrFail();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.service-types.status.update', $record), [
                'is_active' => false,
            ])
            ->assertOk();

        $record->refresh();
        $this->assertFalse($record->is_active);
        $this->assertNotContains('Mulching', MasterCatalog::activeNames(MasterCatalog::SERVICE_TYPES));
    }

    public function test_ajax_filter_returns_matching_records(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('admin.masters.safety-types.index', ['search' => 'Pet']))
            ->assertOk()
            ->assertJsonStructure(['html']);
    }

    public function test_sales_manager_cannot_access_masters(): void
    {
        $sales = User::factory()->create(['is_active' => true]);
        $sales->assignRole('Sales Manager');

        $this->actingAs($sales)
            ->get(route('admin.masters.service-types.index'))
            ->assertForbidden();
    }
}
