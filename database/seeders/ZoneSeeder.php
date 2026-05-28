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
        $zones = collect(range(1, 8))->map(fn (int $i) => [
            'name'       => "Zone {$i}",
            'is_active'  => true,
            'sort_order' => $i,
        ]);
 
        Zone::insert($zones->all());

    }
}
