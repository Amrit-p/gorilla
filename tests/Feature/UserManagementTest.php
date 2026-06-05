<?php

namespace Tests\Feature;

use App\Enums\UserEfficiency;
use App\Enums\UserStatus;
use App\Models\User;
use App\Support\CrmRoles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    public function test_admin_user_has_unique_id_from_seed(): void
    {
        $this->assertNotNull($this->admin->user_unique_id);
        $this->assertGreaterThanOrEqual(1001, $this->admin->user_unique_id);
    }

    public function test_create_user_assigns_unique_id_and_fields(): void
    {
        $payload = $this->validUserPayload([
            'email' => 'sales.new@example.com',
            'role' => CrmRoles::SALES_MANAGER,
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.users.store'), $payload)
            ->assertOk()
            ->assertJsonPath('user.user_unique_id', 1004);

        $this->assertDatabaseHas('users', [
            'email' => 'sales.new@example.com',
            'user_unique_id' => 1004,
            'efficiency' => UserEfficiency::AVERAGE->value,
            'status' => UserStatus::ACTIVE->value,
            'is_active' => true,
        ]);

        $created = User::query()->where('email', 'sales.new@example.com')->first();
        $this->assertTrue($created->hasRole(CrmRoles::SALES_MANAGER));
    }

    public function test_edit_user_via_ajax_updates_fields_and_role(): void
    {
        $user = User::factory()->create([
            'user_unique_id' => 1003,
            'efficiency' => UserEfficiency::BEGINNER->value,
            'status' => UserStatus::ACTIVE->value,
            'is_active' => true,
        ]);
        $user->assignRole(CrmRoles::MOWER);

        $payload = $this->validUserPayload([
            'email' => $user->email,
            'name' => 'Updated Mower Name',
            'efficiency' => UserEfficiency::GOOD->value,
            'status' => UserStatus::ACTIVE->value,
            'role' => CrmRoles::SALES_MANAGER,
            'password' => '',
            'password_confirmation' => '',
        ]);
        unset($payload['password'], $payload['password_confirmation']);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.users.update', $user), $payload)
            ->assertOk();

        $user->refresh();
        $this->assertSame('Updated Mower Name', $user->name);
        $this->assertSame(UserEfficiency::GOOD->value, $user->efficiency);
        $this->assertTrue($user->hasRole(CrmRoles::SALES_MANAGER));
    }

    public function test_duplicate_email_is_rejected_on_create(): void
    {
        $payload = $this->validUserPayload(['email' => $this->admin->email]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.users.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_validation_requires_efficiency_and_status(): void
    {
        $payload = $this->validUserPayload();
        unset($payload['efficiency'], $payload['status']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.users.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['efficiency', 'status']);
    }

    public function test_user_list_filters_by_efficiency(): void
    {
        User::factory()->create([
            'user_unique_id' => 1010,
            'efficiency' => UserEfficiency::GOOD->value,
            'status' => UserStatus::ACTIVE->value,
            'is_active' => true,
        ]);
        User::factory()->create([
            'user_unique_id' => 1011,
            'efficiency' => UserEfficiency::BEGINNER->value,
            'status' => UserStatus::ACTIVE->value,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->getJson(route('admin.users.index', ['efficiency' => UserEfficiency::GOOD->value]))
            ->assertOk()
            ->assertSee('Efficiency', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validUserPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test.user@example.com',
            'phone' => '555-1000',
            'efficiency' => UserEfficiency::AVERAGE->value,
            'status' => UserStatus::ACTIVE->value,
            'role' => CrmRoles::MOWER,
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ], $overrides);
    }
}
