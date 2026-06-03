<?php

namespace App\Policies;

use App\Models\EmployeeBonus;
use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;

class EmployeeBonusPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageEmployeeBonuses($user) || $user->hasRole(CrmRoles::MOWER);
    }

    public function view(User $user, EmployeeBonus $employeeBonus): bool
    {
        return CrmPermissions::canManageEmployeeBonuses($user);
    }

    public function create(User $user): bool
    {
        return CrmPermissions::canManageEmployeeBonuses($user);
    }

    public function update(User $user, EmployeeBonus $employeeBonus): bool
    {
        return CrmPermissions::canManageEmployeeBonuses($user);
    }

    public function delete(User $user, EmployeeBonus $employeeBonus): bool
    {
        return CrmPermissions::canManageEmployeeBonuses($user);
    }
}
