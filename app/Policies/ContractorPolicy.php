<?php

namespace App\Policies;

use App\Models\Contractor;
use App\Models\User;
use App\Support\CrmPermissions;

class ContractorPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageContractors($user);
    }

    public function view(User $user, Contractor $contractor): bool
    {
        return CrmPermissions::canManageContractors($user);
    }

    public function create(User $user): bool
    {
        return CrmPermissions::canManageContractors($user);
    }

    public function update(User $user, Contractor $contractor): bool
    {
        return CrmPermissions::canManageContractors($user);
    }
}
