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
            ChecklistSeeder::class,
            AccountingLevelSeeder::class,
            ClientRatingSeeder::class,
            JobLevelSeeder::class,
            RecurrenceSeeder::class,
        ]);

        // Only seed sample/demo data in non-production environments.
        if (app()->environment(['development', 'dev', 'local'])) {
            $this->call([
                LeadSeeder::class,
                JobSeeder::class,
            ]);
        }
    }
}
