<?php

namespace App\Policies;

use App\Models\Followup;
use App\Models\User;
use App\Support\CrmPermissions;

class FollowupPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageFollowups($user);
    }

    public function view(User $user, Followup $followup): bool
    {
        return CrmPermissions::canManageFollowups($user);
    }

    public function create(User $user): bool
    {
        return CrmPermissions::canManageFollowups($user);
    }

    public function update(User $user, Followup $followup): bool
    {
        return $user->id === $followup->created_by
            || CrmPermissions::isOfficeManager($user);
    }

    public function delete(User $user, Followup $followup): bool
    {
        return $user->id === $followup->created_by
            || CrmPermissions::isOfficeManager($user);
    }
}
