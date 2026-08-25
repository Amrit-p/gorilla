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
                'notes' => 'Called and discussed the job schedule.',
                'status' => FollowupStatus::Pending->value,
                'outcome' => null,
                'next_followup_at' => null,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('followups', [
            'followable_type' => Job::class,
            'followable_id' => 99,
            'notes' => 'Called and discussed the job schedule.',
            'outcome' => null,
            'status' => FollowupStatus::Pending->value,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_notes_are_required_to_create_followup(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => Job::class,
                'followable_id' => 1,
                'notes' => '',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasErrors('notes');
    }

    public function test_outcome_is_optional_when_creating_followup(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => Job::class,
                'followable_id' => 7,
                'notes' => 'No outcome recorded yet.',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('followups', [
            'followable_id' => 7,
            'outcome' => null,
        ]);
    }

    public function test_followable_type_must_be_in_allowed_list(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => 'App\\Models\\NonExistentModel',
                'followable_id' => 1,
                'notes' => 'Some notes',
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
                'notes' => 'Some notes',
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
                'notes' => 'Some notes',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertForbidden();
    }

    // ── General follow-ups ───────────────────────────────────────────────────

    public function test_admin_can_create_a_general_followup_with_only_a_title(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'title' => 'Renew the insurance policy',
                'notes' => 'Broker will send the renewal quote.',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('followups', [
            'followable_type' => null,
            'followable_id' => null,
            'title' => 'Renew the insurance policy',
            'notes' => 'Broker will send the renewal quote.',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_title_is_required_when_no_followable_type_is_given(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'notes' => 'Some notes',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_followable_id_is_required_when_a_followable_type_is_given(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => Job::class,
                'notes' => 'Some notes',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasErrors('followable_id');
    }

    public function test_general_followups_do_not_auto_complete_each_other(): void
    {
        $existing = Followup::query()->create([
            'title' => 'Existing general task',
            'created_by' => $this->admin->id,
            'notes' => 'Still open.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'title' => 'Another general task',
                'notes' => 'Unrelated work.',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('followups', [
            'id' => $existing->id,
            'status' => FollowupStatus::Pending->value,
        ]);
    }

    public function test_index_can_filter_to_general_followups_only(): void
    {
        Followup::query()->create([
            'title' => 'Standalone reminder',
            'created_by' => $this->admin->id,
            'notes' => 'General follow-up note.',
            'status' => FollowupStatus::Pending->value,
        ]);

        Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'notes' => 'Job linked note.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.followups.index', ['followable_type' => Followup::GENERAL_TYPE]))
            ->assertOk()
            ->assertSee('General follow-up note.')
            ->assertDontSee('Job linked note.');
    }

    public function test_general_followup_falls_back_to_its_title_for_labels(): void
    {
        $followup = Followup::query()->create([
            'title' => 'Order new trimmer line',
            'created_by' => $this->admin->id,
            'notes' => 'Two spools.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->assertTrue($followup->isGeneral());
        $this->assertSame('General', $followup->typeLabel());
        $this->assertSame('Order new trimmer line', $followup->subjectLabel());
        $this->assertNull($followup->followableUrl());
    }

    // ── Assignment ───────────────────────────────────────────────────────────

    public function test_admin_can_assign_a_followup_to_a_user(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'title' => 'Chase overdue invoice',
                'notes' => 'Client promised payment this week.',
                'assigned_to' => $this->mower->id,
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('followups', [
            'title' => 'Chase overdue invoice',
            'assigned_to' => $this->mower->id,
        ]);
    }

    public function test_assigned_user_must_exist(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'title' => 'Chase overdue invoice',
                'notes' => 'Some notes',
                'assigned_to' => 999999,
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasErrors('assigned_to');
    }

    public function test_admin_can_reassign_a_followup(): void
    {
        $followup = Followup::query()->create([
            'followable_type' => Lead::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'assigned_to' => $this->admin->id,
            'notes' => 'Original notes.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.followups.update', $followup), [
                'notes' => 'Original notes.',
                'assigned_to' => $this->mower->id,
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertRedirect(route('admin.followups.show', $followup));

        $this->assertDatabaseHas('followups', [
            'id' => $followup->id,
            'assigned_to' => $this->mower->id,
        ]);
    }

    public function test_followup_can_be_unassigned(): void
    {
        $followup = Followup::query()->create([
            'followable_type' => Lead::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'assigned_to' => $this->mower->id,
            'notes' => 'Original notes.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.followups.update', $followup), [
                'notes' => 'Original notes.',
                'assigned_to' => null,
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('followups', [
            'id' => $followup->id,
            'assigned_to' => null,
        ]);
    }

    // ── Read ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_a_followup(): void
    {
        $followup = Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'notes' => 'Reviewed job details.',
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
            'notes' => 'Original notes.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.followups.update', $followup), [
                'notes' => 'Updated notes after call.',
                'outcome' => 'Customer agreed to the quote.',
                'status' => FollowupStatus::Completed->value,
            ])
            ->assertRedirect(route('admin.followups.show', $followup));

        $this->assertDatabaseHas('followups', [
            'id' => $followup->id,
            'notes' => 'Updated notes after call.',
            'outcome' => 'Customer agreed to the quote.',
            'status' => FollowupStatus::Completed->value,
        ]);
    }

    public function test_notes_are_required_to_update_followup(): void
    {
        $followup = Followup::query()->create([
            'followable_type' => Lead::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'notes' => 'Original notes.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.followups.update', $followup), [
                'notes' => '',
                'status' => FollowupStatus::Completed->value,
            ])
            ->assertSessionHasErrors('notes');
    }

    public function test_title_is_required_when_updating_a_general_followup(): void
    {
        $followup = Followup::query()->create([
            'title' => 'Original title',
            'created_by' => $this->admin->id,
            'notes' => 'Original notes.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.followups.update', $followup), [
                'title' => '',
                'notes' => 'Original notes.',
                'status' => FollowupStatus::Pending->value,
            ])
            ->assertSessionHasErrors('title');
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function test_admin_can_delete_a_followup(): void
    {
        $followup = Followup::query()->create([
            'followable_type' => Lead::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'notes' => 'To be deleted.',
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
            'notes' => 'Some notes.',
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
            'notes' => 'First follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        Followup::query()->create([
            'followable_type' => Contractor::class,
            'followable_id' => $contractor->id,
            'created_by' => $this->admin->id,
            'notes' => 'Second follow-up.',
            'status' => FollowupStatus::Completed->value,
        ]);

        // Unrelated record
        Followup::query()->create([
            'followable_type' => Contractor::class,
            'followable_id' => $contractor->id + 1,
            'created_by' => $this->admin->id,
            'notes' => 'Unrelated follow-up.',
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
            'notes' => 'First pending follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $second = Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => $jobId,
            'created_by' => $this->admin->id,
            'notes' => 'Second pending follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        // Follow-up on a different job — must NOT be affected
        $unrelated = Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => $otherJobId,
            'created_by' => $this->admin->id,
            'notes' => 'Unrelated pending follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.followups.store'), [
                'followable_type' => Job::class,
                'followable_id' => $jobId,
                'notes' => 'New follow-up, should close previous ones.',
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
            'notes' => 'Pending follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => 2,
            'created_by' => $this->admin->id,
            'notes' => 'Completed follow-up.',
            'status' => FollowupStatus::Completed->value,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.followups.index', ['status' => FollowupStatus::Completed->value]))
            ->assertOk()
            ->assertSee('Completed follow-up.')
            ->assertDontSee('Pending follow-up.');
    }

    public function test_index_filters_by_assignee(): void
    {
        Followup::query()->create([
            'title' => 'Assigned to the mower',
            'created_by' => $this->admin->id,
            'assigned_to' => $this->mower->id,
            'notes' => 'Mower owns this one.',
            'status' => FollowupStatus::Pending->value,
        ]);

        Followup::query()->create([
            'title' => 'Nobody owns this',
            'created_by' => $this->admin->id,
            'notes' => 'Unassigned note.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.followups.index', ['assigned_to' => $this->mower->id]))
            ->assertOk()
            ->assertSee('Mower owns this one.')
            ->assertDontSee('Unassigned note.');
    }

    public function test_undated_followups_are_filtered_by_their_creation_date(): void
    {
        $undated = Followup::query()->create([
            'title' => 'Undated general task',
            'created_by' => $this->admin->id,
            'notes' => 'No due date was set.',
            'status' => FollowupStatus::Pending->value,
            'next_followup_at' => null,
        ]);
        $undated->forceFill(['created_at' => now()->subDays(3)])->save();

        $dated = Followup::query()->create([
            'title' => 'Dated general task',
            'created_by' => $this->admin->id,
            'notes' => 'Due today.',
            'status' => FollowupStatus::Pending->value,
            'next_followup_at' => today(),
        ]);
        $dated->forceFill(['created_at' => now()->subDays(3)])->save();

        // The day the undated one was created: it shows, the dated one does not.
        $this->actingAs($this->admin)
            ->get(route('admin.followups.index', [
                'next_followup_from' => now()->subDays(3)->toDateString(),
                'next_followup_to' => now()->subDays(3)->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('No due date was set.')
            ->assertDontSee('Due today.');

        // Today: the dated one shows, the undated one does not.
        $this->actingAs($this->admin)
            ->get(route('admin.followups.index', [
                'next_followup_from' => today()->toDateString(),
                'next_followup_to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Due today.')
            ->assertDontSee('No due date was set.');
    }

    public function test_ajax_request_returns_partial_html(): void
    {
        Followup::query()->create([
            'followable_type' => Job::class,
            'followable_id' => 1,
            'created_by' => $this->admin->id,
            'notes' => 'Ajax follow-up.',
            'status' => FollowupStatus::Pending->value,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.followups.index'))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertStringContainsString('Ajax follow-up.', $response->json('html'));
    }
}
