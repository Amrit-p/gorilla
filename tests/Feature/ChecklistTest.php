<?php

namespace Tests\Feature;

use App\Models\Checklist;
use App\Models\ChecklistPoint;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    // -----------------------------------------------------------------------
    // Store
    // -----------------------------------------------------------------------

    public function test_admin_can_store_checklist_with_points(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.checklists.store'), [
                'name'   => 'Safety Checklist',
                'points' => [
                    ['heading' => 'Helmet', 'text' => 'Wear a helmet at all times.', 'sort_order' => 0],
                    ['heading' => 'Vest',   'text' => 'Hi-vis vest required.',        'sort_order' => 1],
                ],
            ])
            ->assertOk()
            ->assertJson(['message' => 'Checklist created successfully.']);

        $checklist = Checklist::query()->where('name', 'Safety Checklist')->firstOrFail();
        $this->assertSame(2, $checklist->points()->count());
    }

    public function test_duplicate_checklist_name_is_rejected(): void
    {
        Checklist::create(['name' => 'Daily Inspection']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.checklists.store'), [
                'name'   => 'Daily Inspection',
                'points' => [
                    ['heading' => 'Check oil', 'text' => 'Check oil level.', 'sort_order' => 0],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_requires_at_least_one_point(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.masters.checklists.store'), [
                'name'   => 'Empty Checklist',
                'points' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['points']);
    }

    // -----------------------------------------------------------------------
    // Show
    // -----------------------------------------------------------------------

    public function test_show_returns_checklist_with_active_points(): void
    {
        $checklist = Checklist::create(['name' => 'Mower Check']);
        ChecklistPoint::create(['checklist_id' => $checklist->id, 'heading' => 'Blades', 'text' => 'Inspect blades.', 'sort_order' => 0]);
        ChecklistPoint::create(['checklist_id' => $checklist->id, 'heading' => 'Fuel',   'text' => 'Check fuel.',     'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->getJson(route('admin.masters.checklists.show', $checklist))
            ->assertOk()
            ->assertJsonCount(2, 'points')
            ->assertJsonFragment(['heading' => 'Blades'])
            ->assertJsonFragment(['heading' => 'Fuel']);
    }

    // -----------------------------------------------------------------------
    // Update — archiving behaviour (primary focus)
    // -----------------------------------------------------------------------

    public function test_removed_point_is_archived_on_update(): void
    {
        $checklist = Checklist::create(['name' => 'Pre-Work Checklist']);
        $keep      = ChecklistPoint::create(['checklist_id' => $checklist->id, 'heading' => 'PPE',    'text' => 'Wear PPE.',    'sort_order' => 0]);
        $remove    = ChecklistPoint::create(['checklist_id' => $checklist->id, 'heading' => 'Gloves', 'text' => 'Wear gloves.', 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.checklists.update', $checklist), [
                'name'   => 'Pre-Work Checklist',
                'points' => [
                    // $remove is intentionally omitted from the submission
                    ['id' => $keep->id, 'heading' => 'PPE', 'text' => 'Wear PPE.', 'sort_order' => 0],
                ],
            ])
            ->assertOk();

        $remove->refresh();
        $this->assertTrue($remove->is_archived);

        // Active relationship still only returns the kept point
        $checklist->refresh();
        $this->assertSame(1, $checklist->points()->count());
        $this->assertSame($keep->id, $checklist->points()->first()->id);
    }

    public function test_content_change_archives_old_point_and_creates_replacement(): void
    {
        $checklist = Checklist::create(['name' => 'Inspection List']);
        $original  = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Old Heading',
            'text'         => 'Original text.',
            'sort_order'   => 0,
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.checklists.update', $checklist), [
                'name'   => 'Inspection List',
                'points' => [
                    ['id' => $original->id, 'heading' => 'New Heading', 'text' => 'Updated text.', 'sort_order' => 0],
                ],
            ])
            ->assertOk();

        // Original is archived
        $original->refresh();
        $this->assertTrue($original->is_archived);

        // A replacement row was created referencing the original
        $replacement = ChecklistPoint::withoutGlobalScope('not_archived')
            ->where('archived_from_point_id', $original->id)
            ->firstOrFail();

        $this->assertSame('New Heading', $replacement->heading);
        $this->assertSame('Updated text.', $replacement->text);
        $this->assertFalse($replacement->is_archived);

        // Active relationship returns the replacement, not the original
        $checklist->refresh();
        $this->assertSame(1, $checklist->points()->count());
        $this->assertSame($replacement->id, $checklist->points()->first()->id);
    }

    public function test_only_text_change_also_triggers_archiving(): void
    {
        $checklist = Checklist::create(['name' => 'Text Change List']);
        $original  = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Same Heading',
            'text'         => 'Original body.',
            'sort_order'   => 0,
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.checklists.update', $checklist), [
                'name'   => 'Text Change List',
                'points' => [
                    ['id' => $original->id, 'heading' => 'Same Heading', 'text' => 'Updated body.', 'sort_order' => 0],
                ],
            ])
            ->assertOk();

        $original->refresh();
        $this->assertTrue($original->is_archived);

        $replacement = ChecklistPoint::withoutGlobalScope('not_archived')
            ->where('archived_from_point_id', $original->id)
            ->firstOrFail();

        $this->assertSame('Updated body.', $replacement->text);
        $this->assertFalse($replacement->is_archived);
    }

    public function test_sort_order_change_does_not_archive_point(): void
    {
        $checklist = Checklist::create(['name' => 'Reorder Checklist']);
        $pointA    = ChecklistPoint::create(['checklist_id' => $checklist->id, 'heading' => 'Step A', 'text' => 'Do A.', 'sort_order' => 0]);
        $pointB    = ChecklistPoint::create(['checklist_id' => $checklist->id, 'heading' => 'Step B', 'text' => 'Do B.', 'sort_order' => 1]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.checklists.update', $checklist), [
                'name'   => 'Reorder Checklist',
                'points' => [
                    ['id' => $pointA->id, 'heading' => 'Step A', 'text' => 'Do A.', 'sort_order' => 1],
                    ['id' => $pointB->id, 'heading' => 'Step B', 'text' => 'Do B.', 'sort_order' => 0],
                ],
            ])
            ->assertOk();

        $pointA->refresh();
        $pointB->refresh();

        $this->assertFalse($pointA->is_archived);
        $this->assertFalse($pointB->is_archived);
        $this->assertSame(1, $pointA->sort_order);
        $this->assertSame(0, $pointB->sort_order);

        // No extra rows were created
        $total = ChecklistPoint::withoutGlobalScope('not_archived')
            ->where('checklist_id', $checklist->id)
            ->count();
        $this->assertSame(2, $total);
    }

    public function test_new_point_can_be_added_during_update(): void
    {
        $checklist = Checklist::create(['name' => 'Growing Checklist']);
        $existing  = ChecklistPoint::create(['checklist_id' => $checklist->id, 'heading' => 'Point 1', 'text' => 'Text 1.', 'sort_order' => 0]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.masters.checklists.update', $checklist), [
                'name'   => 'Growing Checklist',
                'points' => [
                    ['id' => $existing->id, 'heading' => 'Point 1', 'text' => 'Text 1.', 'sort_order' => 0],
                    ['heading' => 'Point 2', 'text' => 'Text 2.', 'sort_order' => 1],
                ],
            ])
            ->assertOk();

        $checklist->refresh();
        $this->assertSame(2, $checklist->points()->count());
        $this->assertDatabaseHas('checklist_points', [
            'checklist_id' => $checklist->id,
            'heading'      => 'Point 2',
            'is_archived'  => false,
        ]);
    }

    // -----------------------------------------------------------------------
    // Global scope — archived points invisible to normal queries
    // -----------------------------------------------------------------------

    public function test_archived_points_are_hidden_from_show_endpoint(): void
    {
        $checklist = Checklist::create(['name' => 'Partial Checklist']);
        ChecklistPoint::create(['checklist_id' => $checklist->id, 'heading' => 'Active',   'text' => 'Visible.', 'sort_order' => 0]);
        ChecklistPoint::create(['checklist_id' => $checklist->id, 'heading' => 'Archived', 'text' => 'Hidden.',  'sort_order' => 1, 'is_archived' => true]);

        $this->actingAs($this->admin)
            ->getJson(route('admin.masters.checklists.show', $checklist))
            ->assertOk()
            ->assertJsonCount(1, 'points')
            ->assertJsonFragment(['heading' => 'Active'])
            ->assertJsonMissing(['heading' => 'Archived']);
    }

    // -----------------------------------------------------------------------
    // Authorization
    // -----------------------------------------------------------------------

    public function test_non_admin_cannot_access_checklists(): void
    {
        $salesManager = User::factory()->create(['is_active' => true]);
        $salesManager->assignRole('Sales Manager');

        $this->actingAs($salesManager)
            ->getJson(route('admin.masters.checklists.index'))
            ->assertForbidden();
    }
}
