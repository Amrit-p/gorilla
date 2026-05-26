<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\ToggleUserStatusRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'efficiency' => $request->string('efficiency')->toString(),
            'role' => $request->string('role')->toString(),
        ];

        $users = $this->userManagementService->paginatedUsers(
            $filters,
            (int) config('mowing.default_pagination', 15)
        );

        $formOptions = $this->userManagementService->formOptions();

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.users.partials.table', compact('users'));
        }

        return view('admin.users.index', array_merge(
            [
                'users' => $users,
                'filterRoles' => Role::query()->orderBy('name')->pluck('name')->all(),
                'filters' => $filters,
            ],
            $formOptions
        ));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', $this->userManagementService->formOptions());
    }

    public function store(StoreUserRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', User::class);
        $user = $this->userManagementService->createUser($request->user(), $request->validated());

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'User created successfully.',
                'user' => [
                    'id' => $user->id,
                    'user_unique_id' => $user->user_unique_id,
                ],
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        $user->load('roles');

        return response()->json([
            'id' => $user->id,
            'user_unique_id' => $user->user_unique_id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'efficiency' => $user->efficiency,
            'status' => $user->status,
            'is_active' => (bool) $user->is_active,
            'role' => $user->roles->pluck('name')->first(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->userManagementService->updateUser($request->user(), $user, $request->validated());

        return response()->json(['message' => 'User updated successfully.']);
    }

    public function updateStatus(ToggleUserStatusRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $this->userManagementService->toggleStatus(
            $request->user(),
            $user,
            (bool) $request->validated('is_active')
        );

        return response()->json(['message' => 'User status updated successfully.']);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userManagementService->deleteUser($request->user(), $user);

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
