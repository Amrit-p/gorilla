<?php

namespace Tests\Feature;

use App\Enums\FollowupStatus;
use App\Models\Contractor;
use App\Models\Followup;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Services\FollowupService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
        $this->mower = User::query()->where('email', 'jake.morrison@mowingcrm.test')->firstOrFail();
    }

    // ── Access control ───────────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_followups_index(): void
    {
        $this->get(route('admin.followups.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_followups_index(): void
    {
        $this->actingAs($this->mower)
            ->get(route('admin.followups.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_followups_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.followups.index'))
            ->assertOk()
            ->assertSee('Follow-ups');
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function test_admin_can_access_followup_create_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.followups.create'))
            ->assertOk();
    }

    public function test_admin_can_create_a_followup(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => Job::class,
                'followable_id' => 99,
                'outcome' => 'Called and discussed the job schedule.',
                'status' => FollowupStatus::Pending->value,
                'notes' => null,
                'next_followup_at' => null,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('followups', [
            'followable_type' => Job::class,
            'followable_id' => 99,
            'outcome' => 'Called and discussed the job schedule.',
            'status' => FollowupStatus::Pending->value,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_outcome_is_required_to_create_followup(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => Job::class,
                'followable_id' => 1,
                'outcome' => '',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasErrors('outcome');
    }

    public function test_followable_type_must_be_in_allowed_list(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => 'App\\Models\\NonExistentModel',
                'followable_id' => 1,
                'outcome' => 'Some outcome',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasErrors('followable_type');
    }

    public function test_status_must_be_valid(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => Job::class,
                'followable_id' => 1,
                'outcome' => 'Some outcome',
                'status' => 'invalid_status',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_non_admin_cannot_create_followup(): void
    {
        $this->actingAs($this->mower)
            ->post(route('admin.followups.store'), [
                'followable_type' => Job::class,
                'followable_id' => 1,
                'outcome' => 'Some outcome',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertForbidden();
    }

    // ── Read ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_a_followup(): void
    {
        $followup = Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'outcome' => 'Reviewed job details.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.followups.show', $followup))
            ->assertOk()
            ->assertSee('Reviewed job details.');
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function test_admin_can_update_a_followup(): void
    {
        $followup = Followup::query()->create([
            'followable_type' => Lead::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'outcome' => 'Original outcome.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.followups.update', $followup), [
                'outcome' => 'Updated outcome after call.',
                'status' => FollowupStatus::Completed->value,
                'notes' => null,
            ])
            ->assertRedirect(route('admin.followups.show', $followup));

        $this->assertDatabaseHas('followups', [
            'id' => $followup->id,
            'outcome' => 'Updated outcome after call.',
            'status' => FollowupStatus::Completed->value,
        ]);
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function test_admin_can_delete_a_followup(): void
    {
        $followup = Followup::query()->create([
            'followable_type' => Lead::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'outcome' => 'To be deleted.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.followups.destroy', $followup))
            ->assertRedirect(route('admin.followups.index'));

        $this->assertDatabaseMissing('followups', ['id' => $followup->id]);
    }

    public function test_non_admin_cannot_delete_a_followup(): void
    {
        $followup = Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'outcome' => 'Some outcome.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->mower)
            ->delete(route('admin.followups.destroy', $followup))
            ->assertForbidden();

        $this->assertDatabaseHas('followups', ['id' => $followup->id]);
    }

    // ── HasFollowups trait ────────────────────────────────────────────────────

    public function test_has_followups_trait_scopes_to_correct_model(): void
    {
        $contractor = Contractor::factory()->create();

        Followup::query()->create([
            'followable_type' => Contractor::class,
            'followable_id' => $contractor->id,
            'created_by' => $this->admin->id,
            'outcome' => 'First follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        Followup::query()->create([
            'followable_type' => Contractor::class,
            'followable_id' => $contractor->id,
            'created_by' => $this->admin->id,
            'outcome' => 'Second follow-up.',
            'status' => FollowupStatus::Completed->value,
        ]);

        // Unrelated record
        Followup::query()->create([
            'followable_type' => Contractor::class,
            'followable_id' => $contractor->id + 1,
            'created_by' => $this->admin->id,
            'outcome' => 'Unrelated follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->assertCount(2, $contractor->followups);
    }

    // ── Auto-complete on create ───────────────────────────────────────────────

    public function test_creating_followup_auto_completes_previous_pending_followups(): void
    {
        $jobId = 42;
        $otherJobId = 99;

        $first = Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => $jobId,
            'created_by' => $this->admin->id,
            'outcome' => 'First pending follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $second = Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => $jobId,
            'created_by' => $this->admin->id,
            'outcome' => 'Second pending follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        // Follow-up on a different job — must NOT be affected
        $unrelated = Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => $otherJobId,
            'created_by' => $this->admin->id,
            'outcome' => 'Unrelated pending follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => Job::class,
                'followable_id' => $jobId,
                'outcome' => 'New follow-up, should close previous ones.',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('followups', ['id' => $first->id, 'status' => FollowupStatus::Completed->value]);
        $this->assertDatabaseHas('followups', ['id' => $second->id, 'status' => FollowupStatus::Completed->value]);
        $this->assertDatabaseHas('followups', ['id' => $unrelated->id, 'status' => FollowupStatus::Pending->value]);
    }

    // ── Allowed followable types ──────────────────────────────────────────────

    public function test_followup_service_allowed_types_include_job_and_lead(): void
    {
        $this->assertContains(Job::class, FollowupService::ALLOWED_FOLLOWABLE_TYPES);
        $this->assertContains(Lead::class, FollowupService::ALLOWED_FOLLOWABLE_TYPES);
    }

    // ── Filtering ────────────────────────────────────────────────────────────

    public function test_index_filters_by_status(): void
    {
        Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'outcome' => 'Pending follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => 2,
            'created_by' => $this->admin->id,
            'outcome' => 'Completed follow-up.',
            'status' => FollowupStatus::Completed->value,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.followups.index', ['status' => FollowupStatus::Completed->value]))
            ->assertOk()
            ->assertSee('Completed follow-up.')
            ->assertDontSee('Pending follow-up.');
    }

    public function test_ajax_request_returns_partial_html(): void
    {
        Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'outcome' => 'Ajax follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.followups.index'))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertStringContainsString('Ajax follow-up.', $response->json('html'));
    }
}
