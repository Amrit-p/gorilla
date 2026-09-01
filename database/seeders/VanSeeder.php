<?php

namespace Database\Seeders;

use App\Models\Van;
use App\Support\MasterCatalog;
use Illuminate\Database\Seeder;

class VanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 2) as $i) {
            Van::firstOrCreate(
                ['name' => "Van {$i}"],
                ['is_active' => true, 'sort_order' => $i],
            );
        }

        MasterCatalog::forgetCache(MasterCatalog::VANS);
    }
}
