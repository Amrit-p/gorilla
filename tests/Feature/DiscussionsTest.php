<?php

namespace Tests\Feature;

use App\Models\Discussion;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscussionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    public function test_admin_can_access_discussions_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.discussions.index'))
            ->assertOk()
            ->assertSee('Discussions');
    }

    public function test_index_search_filters_by_title(): void
    {
        Discussion::factory()->create(['title' => 'Mower Budget Review', 'created_by' => $this->admin->id]);
        Discussion::factory()->create(['title' => 'Expansion Planning', 'created_by' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.discussions.index', ['search' => 'Budget']))
            ->assertOk()
            ->assertSee('Mower Budget Review')
            ->assertDontSee('Expansion Planning');
    }

    public function test_index_filters_by_category(): void
    {
        Discussion::factory()->create(['title' => 'Worker Discussion', 'category' => 'worker', 'created_by' => $this->admin->id]);
        Discussion::factory()->create(['title' => 'Budget Discussion', 'category' => 'budget', 'created_by' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.discussions.index', ['category' => 'worker']))
            ->assertOk()
            ->assertSee('Worker Discussion')
            ->assertDontSee('Budget Discussion');
    }

    public function test_ajax_request_returns_partial_html(): void
    {
        Discussion::factory()->create(['title' => 'Ajax Discussion', 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.discussions.index'))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertStringContainsString('Ajax Discussion', $response->json('html'));
    }
}
