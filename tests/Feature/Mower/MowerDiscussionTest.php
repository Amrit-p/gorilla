<?php

namespace Tests\Feature\Mower;

use App\Http\Middleware\EnsureMowerChecklistComplete;
use App\Models\Discussion;
use App\Models\DiscussionSection;
use App\Models\User;
use App\Support\CrmRoles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MowerDiscussionTest extends TestCase
{
    use RefreshDatabase;

    private User $mower;

    private User $otherMower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->withoutMiddleware(EnsureMowerChecklistComplete::class);

        $this->mower = User::factory()->create(['is_active' => true]);
        $this->mower->assignRole(CrmRoles::MOWER);

        $this->otherMower = User::factory()->create(['is_active' => true]);
        $this->otherMower->assignRole(CrmRoles::MOWER);
    }

    public function test_mower_can_view_their_discussions_list(): void
    {
        Discussion::factory()->forWorker($this->mower)->count(3)->create();
        Discussion::factory()->forWorker($this->otherMower)->count(2)->create();

        $response = $this->actingAs($this->mower)->get(route('mower.discussions.index'));

        $response->assertOk();
        $response->assertViewIs('mower.discussions.index');
        $response->assertViewHas('discussions', fn ($discussions) => $discussions->count() === 3);
    }

    public function test_discussions_index_is_empty_when_none_assigned(): void
    {
        $response = $this->actingAs($this->mower)->get(route('mower.discussions.index'));

        $response->assertOk();
        $response->assertViewHas('discussions', fn ($discussions) => $discussions->isEmpty());
    }

    public function test_mower_can_view_their_own_discussion(): void
    {
        $discussion = Discussion::factory()->forWorker($this->mower)->create();
        DiscussionSection::factory()->create([
            'discussion_id' => $discussion->id,
            'sort_order' => 0,
            'heading' => 'Performance',
            'body' => 'Great work this month.',
        ]);

        $response = $this->actingAs($this->mower)->get(route('mower.discussions.show', $discussion));

        $response->assertOk();
        $response->assertViewIs('mower.discussions.show');
        $response->assertViewHas('discussion', fn ($d) => $d->id === $discussion->id);
    }

    public function test_mower_cannot_view_another_workers_discussion(): void
    {
        $discussion = Discussion::factory()->forWorker($this->otherMower)->create();

        $response = $this->actingAs($this->mower)->get(route('mower.discussions.show', $discussion));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected_from_discussions(): void
    {
        $response = $this->get(route('mower.discussions.index'));

        $response->assertRedirect(route('login'));
    }
}
