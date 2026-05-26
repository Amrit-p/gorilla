<?php

namespace App\Policies;

use App\Models\User;
use App\Support\CrmPermissions;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageUsers($user);
    }

    public function view(User $user, User $model): bool
    {
        return CrmPermissions::canManageUsers($user);
    }

    public function create(User $user): bool
    {
        return CrmPermissions::canManageUsers($user);
    }

    public function update(User $user, User $model): bool
    {
        return CrmPermissions::canManageUsers($user);
    }

    public function delete(User $admin, User $model): bool
    {
        if (! CrmPermissions::canManageUsers($admin)) {
            return false;
        }

        return ! $admin->is($model);
    }
}
