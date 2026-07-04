<?php

namespace App\Policies;

use App\Models\SalaryReceipt;
use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;

class SalaryReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canManageSalaryCalculator($user) || $user->hasRole(CrmRoles::MOWER);
    }

    public function view(User $user, SalaryReceipt $salaryReceipt): bool
    {
        if (CrmPermissions::canManageSalaryCalculator($user)) {
            return true;
        }

        return $user->hasRole(CrmRoles::MOWER) && $user->id === $salaryReceipt->mower_id;
    }

    public function create(User $user): bool
    {
        return CrmPermissions::canManageSalaryCalculator($user);
    }

    public function delete(User $user, SalaryReceipt $salaryReceipt): bool
    {
        return CrmPermissions::canManageSalaryCalculator($user);
    }
}
