<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $this->get(route('dashboard.index'))
            ->assertRedirect(route('login'));
    }

    public function test_office_manager_can_view_dashboard_after_seed(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        /** @var User $admin */
        $admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard.index'))
            ->assertOk();
    }
}
