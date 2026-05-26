<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Support\CrmPermissions;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageLeads($user);
    }

    public function view(User $user, Lead $lead): bool
    {
        return CrmPermissions::canManageLeads($user);
    }

    public function create(User $user): bool
    {
        return CrmPermissions::canManageLeads($user);
    }

    public function update(User $user, Lead $lead): bool
    {
        return CrmPermissions::canManageLeads($user);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return CrmPermissions::canManageLeads($user);
    }
}
