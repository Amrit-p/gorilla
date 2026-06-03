<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Reusable Gorilla CRM permission helpers (gates, policies, routes, Blade).
 */
final class CrmPermissions
{
    public const VIEW_DASHBOARD = 'view_dashboard';

    public const MANAGE_LEADS = 'manage_leads';

    public const MANAGE_CUSTOMERS = 'manage_customers';

    public const MANAGE_JOBS = 'manage_jobs';

    public const ASSIGN_JOBS = 'assign_jobs';

    public const MANAGE_USERS = 'manage_users';

    public const MANAGE_MASTERS = 'manage_masters';

    public const UPLOAD_JOB_IMAGES = 'upload_job_images';

    public const VIEW_MOWER_REPORT = 'view_mower_report';

    public const MANAGE_EMPLOYEE_BONUSES = 'manage_employee_bonuses';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_values(config('permissions.permissions', []));
    }

    public static function guard(): string
    {
        return (string) config('permissions.guard', 'web');
    }

    /**
     * @return array<int, string>
     */
    public static function forRoleKey(string $roleKey): array
    {
        $roleConfig = config("permissions.roles.{$roleKey}");

        if (! is_array($roleConfig)) {
            return [];
        }

        $permissions = $roleConfig['permissions'] ?? [];

        if ($permissions === '*') {
            return self::all();
        }

        return array_values($permissions);
    }

    /**
     * @return array<int, string> Role labels for user create forms.
     */
    public static function assignableRoleLabels(): array
    {
        return self::roleLabelsForKeys(config('permissions.assignable_role_keys', []));
    }

    /**
     * @return array<int, string> Role labels for RBAC dropdown.
     */
    public static function rbacAssignableRoleLabels(): array
    {
        return self::roleLabelsForKeys(config('permissions.rbac_assignable_role_keys', []));
    }

    /**
     * @param  array<int, string>  $roleKeys
     * @return array<int, string>
     */
    public static function roleLabelsForKeys(array $roleKeys): array
    {
        return array_values(array_filter(array_map(
            static fn (string $key): string => CrmRoles::label($key),
            $roleKeys
        )));
    }

    public static function canViewDashboard(?User $user): bool
    {
        return $user?->can(self::VIEW_DASHBOARD) ?? false;
    }

    public static function canManageLeads(?User $user): bool
    {
        return $user?->can(self::MANAGE_LEADS) ?? false;
    }

    public static function canManageCustomers(?User $user): bool
    {
        return $user?->can(self::MANAGE_CUSTOMERS) ?? false;
    }

    /** List jobs, maps (read), mobile dashboard. */
    public static function canViewJobs(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can(self::MANAGE_JOBS) || $user->can(self::ASSIGN_JOBS);
    }

    /** Create, update, delete job records (scheduling CRUD). */
    public static function canManageJobRecords(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can(self::MANAGE_JOBS) && $user->can(self::ASSIGN_JOBS);
    }

    public static function canAssignJobs(?User $user): bool
    {
        return $user?->can(self::ASSIGN_JOBS) ?? false;
    }

    public static function canManageUsers(?User $user): bool
    {
        return $user?->can(self::MANAGE_USERS) ?? false;
    }

    public static function canManageMasters(?User $user): bool
    {
        return $user?->can(self::MANAGE_MASTERS) ?? false;
    }

    public static function canUploadJobImages(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can(self::UPLOAD_JOB_IMAGES) || self::canManageJobRecords($user);
    }

    public static function isOfficeManager(?User $user): bool
    {
        return $user?->hasRole(CrmRoles::OFFICE_MANAGER) ?? false;
    }

    public static function canManageEmployeeBonuses(?User $user): bool
    {
        return $user?->can(self::MANAGE_EMPLOYEE_BONUSES) ?? false;
    }

    public static function canViewReport(?User $user, string $permission): bool
    {
        return $user?->can($permission) ?? false;
    }

    /**
     * Seed or upgrade permissions and roles from config.
     */
    public static function syncRolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = self::guard();

        foreach (self::all() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);
        }

        $allPermissions = Permission::query()->where('guard_name', $guard)->get();

        foreach (config('permissions.roles', []) as $roleKey => $roleConfig) {
            $role = Role::query()->firstOrCreate([
                'name' => (string) $roleConfig['label'],
                'guard_name' => $guard,
            ]);

            $permissionNames = self::forRoleKey($roleKey);
            $permissions = $permissionNames === self::all()
                ? $allPermissions
                : $allPermissions->whereIn('name', $permissionNames);

            $role->syncPermissions($permissions);
        }
    }

    /**
     * Migrate legacy role/permission names on existing databases.
     */
    public static function migrateLegacyAssignments(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        self::syncRolesAndPermissions();

        $guard = self::guard();

        foreach (config('permissions.legacy_permissions', []) as $oldName => $newName) {
            $old = Permission::query()->where('name', $oldName)->where('guard_name', $guard)->first();
            if (! $old) {
                continue;
            }

            $new = Permission::query()->where('name', $newName)->where('guard_name', $guard)->first();
            if (! $new) {
                continue;
            }

            // Drop legacy pivots when the target permission is already assigned (syncRolesAndPermissions may have added it).
            $roleIdsWithNewPermission = \Illuminate\Support\Facades\DB::table('role_has_permissions')
                ->where('permission_id', $new->id)
                ->pluck('role_id');

            if ($roleIdsWithNewPermission->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('role_has_permissions')
                    ->where('permission_id', $old->id)
                    ->whereIn('role_id', $roleIdsWithNewPermission)
                    ->delete();
            }

            \Illuminate\Support\Facades\DB::table('role_has_permissions')
                ->where('permission_id', $old->id)
                ->update(['permission_id' => $new->id]);

            $modelsWithNewPermission = \Illuminate\Support\Facades\DB::table('model_has_permissions')
                ->where('permission_id', $new->id)
                ->get(['model_id', 'model_type']);

            if ($modelsWithNewPermission->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('model_has_permissions')
                    ->where('permission_id', $old->id)
                    ->where(function ($query) use ($modelsWithNewPermission) {
                        foreach ($modelsWithNewPermission as $assignment) {
                            $query->orWhere(function ($nested) use ($assignment) {
                                $nested->where('model_id', $assignment->model_id)
                                    ->where('model_type', $assignment->model_type);
                            });
                        }
                    })
                    ->delete();
            }

            \Illuminate\Support\Facades\DB::table('model_has_permissions')
                ->where('permission_id', $old->id)
                ->update(['permission_id' => $new->id]);

            $old->delete();
        }

        foreach (config('permissions.legacy_roles', []) as $oldLabel => $newRoleKey) {
            self::mergeRoleInto($oldLabel, CrmRoles::label($newRoleKey), $guard);
        }

        foreach (['Sales Person', 'Super Admin', 'Mowers', 'Employee', 'Sales', 'Manager'] as $obsolete) {
            $role = Role::query()->where('name', $obsolete)->where('guard_name', $guard)->first();
            if ($role) {
                $role->delete();
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Move users from a legacy role into the canonical Gorilla role, then remove the legacy role.
     */
    public static function mergeRoleInto(string $fromLabel, string $toLabel, ?string $guard = null): void
    {
        $guard ??= self::guard();

        if ($fromLabel === $toLabel) {
            return;
        }

        $from = Role::query()->where('name', $fromLabel)->where('guard_name', $guard)->first();
        $to = Role::query()->where('name', $toLabel)->where('guard_name', $guard)->first();

        if (! $from) {
            return;
        }

        if (! $to) {
            $from->update(['name' => $toLabel]);

            return;
        }

        \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->where('role_id', $from->id)
            ->update(['role_id' => $to->id]);

        \Illuminate\Support\Facades\DB::table('role_has_permissions')
            ->where('role_id', $from->id)
            ->delete();

        $from->delete();
    }
}
