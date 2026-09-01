<?php

namespace Tests\Feature\Mower;

use App\Models\Checklist;
use App\Models\MowerChecklistSubmission;
use App\Models\User;
use App\Models\Van;
use App\Models\VanAssignment;
use App\Support\CrmRoles;
use Database\Seeders\ChecklistSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MowerVanSelectionTest extends TestCase
{
    use RefreshDatabase;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(ChecklistSeeder::class);
        $this->seed(MasterCatalogSeeder::class);

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);
        $this->completeChecklistForMower($this->mower);
    }

    public function test_checklist_submit_redirects_to_van_selection(): void
    {
        $mower = User::factory()->create(['is_active' => true]);
        $mower->assignRole(CrmRoles::MOWER);

        $pointIds = Checklist::safetyChecklist()->points->pluck('id')->all();

        $response = $this->actingAs($mower)->post(route('mower.checklist.submit'), [
            'point_ids' => $pointIds,
            'acknowledged' => '1',
        ]);

        $response->assertRedirect(route('mower.van.select'));
    }

    public function test_mower_without_a_van_is_redirected_to_van_selection(): void
    {
        Van::create(['name' => 'VAN-1', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertRedirect(route('mower.van.select'));
    }

    public function test_dashboard_is_accessible_when_no_vans_are_configured(): void
    {
        // No vans set up yet: mowers should not be blocked.
        $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertOk();
    }

    public function test_mower_can_select_an_available_van(): void
    {
        $van = Van::create(['name' => 'VAN-1', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->mower)
            ->post(route('mower.van.store'), ['van_id' => $van->id])
            ->assertRedirect(route('mower.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('van_assignments', [
            'van_id' => $van->id,
            'user_id' => $this->mower->id,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_mower_cannot_select_a_van_taken_by_another_mower_today(): void
    {
        $van = Van::create(['name' => 'VAN-1', 'is_active' => true, 'sort_order' => 1]);

        $otherMower = User::factory()->create(['is_active' => true]);
        $otherMower->assignRole(CrmRoles::MOWER);
        VanAssignment::create([
            'van_id' => $van->id,
            'user_id' => $otherMower->id,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($this->mower)
            ->post(route('mower.van.store'), ['van_id' => $van->id])
            ->assertSessionHasErrors('van_id');

        $this->assertDatabaseMissing('van_assignments', [
            'van_id' => $van->id,
            'user_id' => $this->mower->id,
        ]);
    }

    public function test_selecting_a_new_van_replaces_the_mowers_previous_van(): void
    {
        $van1 = Van::create(['name' => 'VAN-1', 'is_active' => true, 'sort_order' => 1]);
        $van2 = Van::create(['name' => 'VAN-2', 'is_active' => true, 'sort_order' => 2]);

        VanAssignment::create([
            'van_id' => $van1->id,
            'user_id' => $this->mower->id,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($this->mower)
            ->post(route('mower.van.store'), ['van_id' => $van2->id])
            ->assertRedirect(route('mower.index'));

        $this->assertSame(
            1,
            VanAssignment::where('user_id', $this->mower->id)->where('date', now()->toDateString())->count()
        );
        $this->assertDatabaseHas('van_assignments', [
            'user_id' => $this->mower->id,
            'van_id' => $van2->id,
        ]);
    }

    private function completeChecklistForMower(User $mower): void
    {
        $today = now()->toDateString();

        foreach (Checklist::safetyChecklist()->points as $point) {
            MowerChecklistSubmission::create([
                'user_id' => $mower->id,
                'checklist_point_id' => $point->id,
                'date' => $today,
            ]);
        }
    }
}
