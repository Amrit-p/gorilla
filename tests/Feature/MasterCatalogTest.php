<?php

namespace Tests\Feature;

use App\Models\AccountingLevel;
use App\Models\EquipmentType;
use App\Models\JobLevel;
use App\Models\Recurrence;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Zone;
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

    public function test_zone_crud_and_cache(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.zones.store'), [
                'name' => 'North Zone',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertContains('North Zone', MasterCatalog::activeNames(MasterCatalog::ZONES));

        $record = Zone::query()->where('name', 'North Zone')->firstOrFail();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.zones.update', $record), [
                'name' => 'North Zone Updated',
                'sort_order' => 2,
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('zones', ['name' => 'North Zone Updated']);
    }

    public function test_duplicate_zone_name_is_rejected(): void
    {
        Zone::create(['name' => 'East Zone', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.zones.store'), [
                'name' => 'East Zone',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_zone_status_toggle_deactivates_record(): void
    {
        $record = Zone::create(['name' => 'West Zone', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.zones.status.update', $record), [
                'is_active' => false,
            ])
            ->assertOk();

        $record->refresh();
        $this->assertFalse($record->is_active);
        $this->assertNotContains('West Zone', MasterCatalog::activeNames(MasterCatalog::ZONES));
    }

    public function test_recurrence_crud_and_cache(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.recurrences.store'), [
                'name' => 'Weekly',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertContains('Weekly', MasterCatalog::activeNames(MasterCatalog::RECURRENCES));

        $record = Recurrence::query()->where('name', 'Weekly')->firstOrFail();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.recurrences.update', $record), [
                'name' => 'Bi-Weekly',
                'sort_order' => 2,
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('recurrences', ['name' => 'Bi-Weekly']);
    }

    public function test_duplicate_recurrence_name_is_rejected(): void
    {
        Recurrence::create(['name' => 'Monthly', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.recurrences.store'), [
                'name' => 'Monthly',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_recurrence_status_toggle_deactivates_record(): void
    {
        $record = Recurrence::create(['name' => 'Fortnightly', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.recurrences.status.update', $record), [
                'is_active' => false,
            ])
            ->assertOk();

        $record->refresh();
        $this->assertFalse($record->is_active);
        $this->assertNotContains('Fortnightly', MasterCatalog::activeNames(MasterCatalog::RECURRENCES));
    }

    public function test_accounting_level_crud_and_cache(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.accounting-levels.store'), [
                'name' => 'Bronze',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertContains('Bronze', MasterCatalog::activeNames(MasterCatalog::ACCOUNTING_LEVELS));

        $record = AccountingLevel::query()->where('name', 'Bronze')->firstOrFail();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.accounting-levels.update', $record), [
                'name' => 'Bronze Updated',
                'sort_order' => 2,
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('accounting_levels', ['name' => 'Bronze Updated']);
    }

    public function test_duplicate_accounting_level_name_is_rejected(): void
    {
        AccountingLevel::create(['name' => 'Silver', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.accounting-levels.store'), [
                'name' => 'Silver',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_accounting_level_status_toggle_deactivates_record(): void
    {
        $record = AccountingLevel::create(['name' => 'Gold', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.accounting-levels.status.update', $record), [
                'is_active' => false,
            ])
            ->assertOk();

        $record->refresh();
        $this->assertFalse($record->is_active);
        $this->assertNotContains('Gold', MasterCatalog::activeNames(MasterCatalog::ACCOUNTING_LEVELS));
    }

    public function test_job_level_requires_valid_color_code(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.job-levels.store'), [
                'name' => 'Junior',
                'color_code' => 'not-a-color',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['color_code']);
    }

    public function test_job_level_crud_with_color(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.job-levels.store'), [
                'name' => 'Senior',
                'color_code' => '#ff6600',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertOk();

        $record = JobLevel::query()->where('name', 'Senior')->firstOrFail();
        $this->assertSame('#ff6600', $record->color_code);
    }

    public function test_duplicate_job_level_name_is_rejected(): void
    {
        JobLevel::create(['name' => 'Lead', 'color_code' => '#aabbcc', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.job-levels.store'), [
                'name' => 'Lead',
                'color_code' => '#aabbcc',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_job_level_status_toggle_deactivates_record(): void
    {
        $record = JobLevel::create(['name' => 'Intern', 'color_code' => '#123456', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.job-levels.status.update', $record), [
                'is_active' => false,
            ])
            ->assertOk();

        $record->refresh();
        $this->assertFalse($record->is_active);
        $this->assertNotContains('Intern', MasterCatalog::activeNames(MasterCatalog::JOB_LEVELS));
    }
}
