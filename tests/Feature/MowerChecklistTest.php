<?php

namespace Tests\Feature;

use App\Models\Checklist;
use App\Models\ChecklistPoint;
use App\Models\MowerChecklistSubmission;
use App\Models\User;
use App\Support\CrmRoles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MowerChecklistTest extends TestCase
{
    use RefreshDatabase;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);
    }

    // SQLite stores Eloquent date-cast attributes as "Y-m-d H:i:s" (using
    // the model's dateFormat), but the WHERE clauses compare against "Y-m-d"
    // strings. Raw DB inserts store the plain date string so the queries match.
    private function createSubmission(int $pointId, ?string $date = null): void
    {
        DB::table('mower_checklist_submissions')->insert([
            'user_id'            => $this->mower->id,
            'checklist_point_id' => $pointId,
            'date'               => $date ?? now()->toDateString(),
            'created_at'         => now()->toDateTimeString(),
            'updated_at'         => now()->toDateTimeString(),
        ]);
    }

    // -----------------------------------------------------------------------
    // MowerChecklistSubmission — model
    // -----------------------------------------------------------------------

    public function test_submission_belongs_to_user(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $point = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);

        $submission = MowerChecklistSubmission::create([
            'user_id'            => $this->mower->id,
            'checklist_point_id' => $point->id,
            'date'               => today()->toDateString(),
        ]);

        $this->assertTrue($submission->user->is($this->mower));
    }

    public function test_submission_belongs_to_checklist_point(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $point = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Vest',
            'text'         => 'Wear a hi-vis vest.',
            'sort_order'   => 0,
        ]);

        $submission = MowerChecklistSubmission::create([
            'user_id'            => $this->mower->id,
            'checklist_point_id' => $point->id,
            'date'               => today()->toDateString(),
        ]);

        $this->assertTrue($submission->checklistPoint->is($point));
    }

    public function test_date_is_cast_to_carbon_date_instance(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $point = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Blades',
            'text'         => 'Inspect blades.',
            'sort_order'   => 0,
        ]);

        $submission = MowerChecklistSubmission::create([
            'user_id'            => $this->mower->id,
            'checklist_point_id' => $point->id,
            'date'               => '2025-06-01',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $submission->date);
        $this->assertSame('2025-06-01', $submission->date->toDateString());
    }

    // -----------------------------------------------------------------------
    // EnsureMowerChecklistComplete — middleware
    // -----------------------------------------------------------------------

    public function test_non_mower_user_bypasses_checklist_middleware(): void
    {
        $admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('mower.index'))
            ->assertOk();
    }

    public function test_mower_without_safety_checklist_in_db_gets_503(): void
    {
        // No Checklist row exists at all
        $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertStatus(503);
    }

    public function test_mower_with_checklist_that_has_no_active_points_gets_503(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        // Archive the only point so no active points remain
        ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Archived',
            'text'         => 'This point is archived.',
            'sort_order'   => 0,
            'is_archived'  => true,
        ]);

        $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertStatus(503);
    }

    public function test_mower_who_completed_all_points_today_bypasses_middleware(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $point = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);

        $this->createSubmission($point->id);

        $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertOk();
    }

    public function test_mower_with_no_submissions_today_is_redirected_to_checklist(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);

        $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertRedirect(route('mower.checklist.index'));
    }

    public function test_mower_with_partial_submissions_is_redirected_to_checklist(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $pointA = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);
        ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Vest',
            'text'         => 'Wear a vest.',
            'sort_order'   => 1,
        ]);

        $this->createSubmission($pointA->id);

        $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertRedirect(route('mower.checklist.index'));
    }

    public function test_yesterdays_submission_does_not_satisfy_todays_checklist(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $point = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);

        $this->createSubmission($point->id, now()->subDay()->toDateString());

        $this->actingAs($this->mower)
            ->get(route('mower.index'))
            ->assertRedirect(route('mower.checklist.index'));
    }

    // -----------------------------------------------------------------------
    // ChecklistController — index & submit
    // -----------------------------------------------------------------------

    public function test_checklist_index_shows_form_when_not_yet_submitted(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);

        $this->actingAs($this->mower)
            ->get(route('mower.checklist.index'))
            ->assertOk()
            ->assertViewIs('mower.checklist');
    }

    public function test_checklist_index_redirects_to_dashboard_when_already_complete(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $point = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);

        $this->createSubmission($point->id);

        $this->actingAs($this->mower)
            ->get(route('mower.checklist.index'))
            ->assertRedirect(route('mower.index'));
    }

    public function test_mower_can_submit_all_checklist_points(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $pointA = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);
        $pointB = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Vest',
            'text'         => 'Wear a vest.',
            'sort_order'   => 1,
        ]);

        $this->actingAs($this->mower)
            ->post(route('mower.checklist.submit'), [
                'point_ids'    => [$pointA->id, $pointB->id],
                'acknowledged' => '1',
            ])
            ->assertRedirect(route('mower.index'));

        $this->assertDatabaseCount('mower_checklist_submissions', 2);
        $this->assertDatabaseHas('mower_checklist_submissions', [
            'user_id'            => $this->mower->id,
            'checklist_point_id' => $pointA->id,
        ]);
    }

    public function test_submit_is_idempotent_and_does_not_create_duplicate_rows(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $point = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);

        $payload = ['point_ids' => [$point->id], 'acknowledged' => '1'];

        $this->actingAs($this->mower)->post(route('mower.checklist.submit'), $payload);
        $this->actingAs($this->mower)->post(route('mower.checklist.submit'), $payload);

        $this->assertDatabaseCount('mower_checklist_submissions', 1);
    }

    public function test_submit_fails_if_not_all_active_points_are_included(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);
        $pointB = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Vest',
            'text'         => 'Wear a vest.',
            'sort_order'   => 1,
        ]);

        // Only submitting one of two required points
        $this->actingAs($this->mower)
            ->post(route('mower.checklist.submit'), [
                'point_ids'    => [$pointB->id],
                'acknowledged' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('point_ids');

        $this->assertDatabaseEmpty('mower_checklist_submissions');
    }

    public function test_submit_requires_acknowledgement(): void
    {
        $checklist = Checklist::create(['name' => 'Safety Checklist']);
        $point = ChecklistPoint::create([
            'checklist_id' => $checklist->id,
            'heading'      => 'Helmet',
            'text'         => 'Wear a helmet.',
            'sort_order'   => 0,
        ]);

        $this->actingAs($this->mower)
            ->post(route('mower.checklist.submit'), [
                'point_ids'    => [$point->id],
                'acknowledged' => '0',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('acknowledged');

        $this->assertDatabaseEmpty('mower_checklist_submissions');
    }

    public function test_submit_requires_point_ids(): void
    {
        Checklist::create(['name' => 'Safety Checklist']);

        $this->actingAs($this->mower)
            ->post(route('mower.checklist.submit'), [
                'point_ids'    => [],
                'acknowledged' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('point_ids');
    }
}
