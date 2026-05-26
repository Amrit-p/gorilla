<?php

namespace Database\Seeders;

use App\Enums\UserEfficiency;
use App\Enums\UserStatus;
use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        CrmPermissions::syncRolesAndPermissions();

        $officeManager = User::query()->firstOrCreate(
            ['email' => 'admin@mowingcrm.test'],
            [
                'name' => 'Office Manager',
                'phone' => null,
                'efficiency' => UserEfficiency::GOOD->value,
                'status' => UserStatus::ACTIVE->value,
                'password' => Hash::make('Password@123'),
                'is_active' => true,
            ]
        );

        $officeManager->syncRoles([CrmRoles::OFFICE_MANAGER]);
    }
}
