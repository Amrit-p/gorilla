<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ActivityLogService $activityLogService
    ) {}

    public function paginatedUsers(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->userRepository->paginatedList($filters, $perPage);
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return [
            'roles' => config('roles.assignable', []),
            'efficiencies' => \App\Enums\UserEfficiency::values(),
            'statuses' => UserStatus::values(),
        ];
    }

    public function createUser(User $adminUser, array $data): User
    {
        $status = (string) $data['status'];

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'efficiency' => $data['efficiency'],
            'status' => $status,
            'password' => Hash::make($data['password']),
            'is_active' => $status === UserStatus::ACTIVE->value,
        ]);

        $user->syncRoles([$data['role']]);

        $this->activityLogService->log($adminUser, 'user.created', 'Admin created user.', [
            'target_user_id' => $user->id,
            'user_unique_id' => $user->user_unique_id,
            'target_user_email' => $user->email,
            'role' => $data['role'],
        ]);

        return $user;
    }

    public function updateUser(User $adminUser, User $user, array $data): User
    {
        $status = (string) $data['status'];

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'efficiency' => $data['efficiency'],
            'status' => $status,
            'is_active' => $status === UserStatus::ACTIVE->value,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        $this->activityLogService->log($adminUser, 'user.updated', 'Admin updated user.', [
            'target_user_id' => $user->id,
            'user_unique_id' => $user->user_unique_id,
            'target_user_email' => $user->email,
            'role' => $data['role'],
        ]);

        return $user;
    }

    public function toggleStatus(User $adminUser, User $user, bool $isActive): void
    {
        $user->is_active = $isActive;
        $user->status = UserStatus::fromActiveFlag($isActive)->value;
        $user->save();

        $this->activityLogService->log($adminUser, 'user.status_updated', 'Admin changed user status.', [
            'target_user_id' => $user->id,
            'user_unique_id' => $user->user_unique_id,
            'target_user_email' => $user->email,
            'status' => $user->status,
        ]);
    }

    public function deleteUser(User $adminUser, User $user): void
    {
        abort_if($adminUser->is($user), 403, 'You cannot delete your own account.');

        $user->delete();

        $this->activityLogService->log($adminUser, 'user.deleted', 'Admin soft deleted user.', [
            'target_user_id' => $user->id,
            'user_unique_id' => $user->user_unique_id,
            'target_user_email' => $user->email,
        ]);
    }
}
