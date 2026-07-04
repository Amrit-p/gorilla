<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use App\Support\CrmPermissions;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageCustomers($user);
    }

    public function view(User $user, Client $client): bool
    {
        return CrmPermissions::canManageCustomers($user);
    }

    public function create(User $user): bool
    {
        return CrmPermissions::canManageCustomers($user);
    }

    public function update(User $user, Client $client): bool
    {
        return CrmPermissions::canManageCustomers($user);
    }

    public function delete(User $user, Client $client): bool
    {
        return CrmPermissions::canManageCustomers($user);
    }

    public function restore(User $user, Client $client): bool
    {
        return CrmPermissions::canManageCustomers($user);
    }
}
