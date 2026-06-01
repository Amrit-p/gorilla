<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed RBAC first so all modules can rely on permissions.
        $this->call([
            RoleAndPermissionSeeder::class,
            MasterCatalogSeeder::class,
            SettingsSeeder::class,
            ZoneSeeder::class,
            AccountingLevelSeeder::class,
            RecurrenceSeeder::class,
            LeadSeeder::class,
            ClientSeeder::class,
            JobSeeder::class,
        ]);
    }
}
