<?php

namespace Database\Seeders;

use App\Models\Recurrence;
use Illuminate\Database\Seeder;

class RecurrenceSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['One-Time', 'Daily', 'Weekly', 'Bi-Weekly', 'Monthly', 'Quarterly'];

        foreach ($names as $i => $name) {
            Recurrence::firstOrCreate(
                ['name' => $name],
                ['is_active' => true, 'sort_order' => $i + 1],
            );
        }
    }
}
