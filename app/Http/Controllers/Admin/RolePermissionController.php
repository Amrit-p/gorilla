<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\Auth\RolePermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function __construct(
        private readonly RolePermissionService $rolePermissionService
    ) {}

    /**
     * Show user-role assignment screen.
     */
    public function index(): View
    {
        return view('admin.rbac.index', [
            'users' => $this->rolePermissionService->paginatedUsersWithRoles(
                (int) config('mowing.default_pagination', 15)
            ),
            'roles' => $this->rolePermissionService->availableRoles(),
        ]);
    }

    /**
     * Update selected user's role through AJAX.
     */
    public function updateUserRole(UpdateUserRoleRequest $request, User $user): JsonResponse
    {
        $this->rolePermissionService->syncUserRole($request->user(), $user, $request->validated('role'));

        return response()->json([
            'message' => 'Role updated successfully.',
        ]);
    }
}
