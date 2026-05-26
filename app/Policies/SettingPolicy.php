<?php

namespace App\Policies;

use App\Models\User;
use App\Support\CrmPermissions;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageUsers($user);
    }

    public function update(User $user): bool
    {
        return CrmPermissions::canManageUsers($user);
    }
}
