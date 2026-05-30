<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 8) as $i) {
            Zone::firstOrCreate(
                ['name' => "Zone {$i}"],
                ['is_active' => true, 'sort_order' => $i],
            );
        }

    }
}
