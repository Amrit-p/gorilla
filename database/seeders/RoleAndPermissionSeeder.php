<?php

namespace Database\Seeders;

use App\Enums\UserEfficiency;
use App\Enums\UserStatus;
use App\Models\User;
use App\Support\CrmConstants;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        CrmPermissions::syncRolesAndPermissions();

        $admin = User::query()->firstOrCreate(
            ['email' => CrmConstants::ADMIN_EMAIL],
            [
                'name' => 'Admin',
                'phone' => null,
                'efficiency' => UserEfficiency::GOOD->value,
                'status' => UserStatus::ACTIVE->value,
                'password' => Hash::make('Password@123'),
                'is_active' => true,
            ]
        );

        $admin->syncRoles([CrmRoles::ADMIN]);

        $officeManager = User::query()->firstOrCreate(
            ['email' => CrmConstants::OFFICE_MANAGER_EMAIL],
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

        $mowers = [
            ['name' => 'Jake Morrison', 'email' => 'jake.morrison@mowingcrm.test', 'efficiency' => UserEfficiency::GOOD->value],
            ['name' => 'Liam Carter',   'email' => 'liam.carter@mowingcrm.test',   'efficiency' => UserEfficiency::AVERAGE->value],
        ];

        foreach ($mowers as $data) {
            $mower = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => null,
                    'efficiency' => $data['efficiency'],
                    'status' => UserStatus::ACTIVE->value,
                    'incentive_percentage' => $data['efficiency'] === UserEfficiency::GOOD->value ? 10 : 5,
                    'password' => Hash::make('Password@123'),
                    'is_active' => true,
                ]
            );

            $mower->syncRoles([CrmRoles::MOWER]);
        }
    }
}
