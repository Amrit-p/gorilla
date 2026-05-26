<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Support\CrmPermissions;

class RolePermissionService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {
    }

    /**
     * Paginated users with current role assignment for admin table.
     */
    public function paginatedUsersWithRoles(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Return available roles to populate role dropdown.
     */
    public function availableRoles(): array
    {
        return CrmPermissions::rbacAssignableRoleLabels();
    }

    /**
     * Replace current role with selected role for given user.
     */
    public function syncUserRole(User $adminUser, User $targetUser, string $role): void
    {
        $targetUser->syncRoles([$role]);

        $this->activityLogService->log(
            $adminUser,
            'rbac.role_updated',
            'User role updated by administrator.',
            [
                'target_user_id' => $targetUser->id,
                'target_user_email' => $targetUser->email,
                'assigned_role' => $role,
            ]
        );
    }
}
