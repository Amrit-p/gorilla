<?php

namespace App\Policies;

use App\Models\User;
use App\Support\CrmPermissions;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageUsers($user);
    }
}
