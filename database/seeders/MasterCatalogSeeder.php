<?php

namespace Database\Seeders;

use App\Models\EquipmentType;
use App\Models\SafetyType;
use App\Models\ServiceType;
use App\Support\MasterCatalog;
use Illuminate\Database\Seeder;

class MasterCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $serviceTypes = [
            ['name' => 'Mulching', 'sort_order' => 1],
            ['name' => 'Side Shoot', 'sort_order' => 2],
            ['name' => 'Cut & Leave', 'sort_order' => 3],
            ['name' => 'Cut & Away Side Shoot', 'sort_order' => 4],
        ];

        foreach ($serviceTypes as $row) {
            ServiceType::query()->firstOrCreate(
                ['name' => $row['name']],
                ['is_active' => true, 'sort_order' => $row['sort_order']]
            );
        }

        $equipmentTypes = [
            ['name' => 'Red Mower', 'color_code' => '#ef4444', 'sort_order' => 1],
            ['name' => 'Yellow Mower', 'color_code' => '#eab308', 'sort_order' => 2],
            ['name' => 'Honda', 'color_code' => '#3b82f6', 'sort_order' => 3],
            ['name' => 'Mulcher', 'color_code' => '#22c55e', 'sort_order' => 4],
        ];

        foreach ($equipmentTypes as $row) {
            EquipmentType::query()->firstOrCreate(
                ['name' => $row['name']],
                [
                    'color_code' => $row['color_code'],
                    'is_active' => true,
                    'sort_order' => $row['sort_order'],
                ]
            );
        }

        $safetyTypes = [
            ['name' => 'Pet', 'sort_order' => 1],
            ['name' => 'Public', 'sort_order' => 2],
            ['name' => 'Vehicles', 'sort_order' => 3],
            ['name' => 'Storms', 'sort_order' => 4],
            ['name' => 'Stones', 'sort_order' => 5],
            ['name' => 'Any Other', 'sort_order' => 6],
        ];

        foreach ($safetyTypes as $row) {
            SafetyType::query()->firstOrCreate(
                ['name' => $row['name']],
                ['is_active' => true, 'sort_order' => $row['sort_order']]
            );
        }

        MasterCatalog::forgetCache(MasterCatalog::SERVICE_TYPES);
        MasterCatalog::forgetCache(MasterCatalog::EQUIPMENT_TYPES);
        MasterCatalog::forgetCache(MasterCatalog::SAFETY_TYPES);
    }
}
