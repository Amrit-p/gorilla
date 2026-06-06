<?php

namespace App\Policies;

use App\Models\Checklist;
use App\Models\User;
use App\Support\CrmPermissions;

class ChecklistPolicy
{
    public function viewAny(?User $user): bool
    {
        return CrmPermissions::canManageMasters($user);
    }

    public function create(?User $user): bool
    {
        return CrmPermissions::canManageMasters($user);
    }

    public function view(?User $user, Checklist $_checklist): bool
    {
        return CrmPermissions::canManageMasters($user);
    }

    public function update(?User $user, Checklist $_checklist): bool
    {
        return CrmPermissions::canManageMasters($user);
    }
}
