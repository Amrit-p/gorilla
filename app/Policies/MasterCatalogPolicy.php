<?php

namespace App\Policies;

use App\Models\User;
use App\Support\CrmPermissions;
use Illuminate\Database\Eloquent\Model;

class MasterCatalogPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageMasters($user);
    }

    public function create(User $user): bool
    {
        return CrmPermissions::canManageMasters($user);
    }

    public function update(User $user, Model $record): bool
    {
        return CrmPermissions::canManageMasters($user);
    }

    public function delete(User $user, Model $record): bool
    {
        return CrmPermissions::canManageMasters($user);
    }
}
