<?php

namespace Database\Seeders;

use App\Models\Recurrence;
use Illuminate\Database\Seeder;

class RecurrenceSeeder extends Seeder
{
    public function run(): void
    {
        $recurrences = [
            'One-Time' => null,
            'Daily' => 1,
            'Weekly' => 7,
            'Bi-Weekly' => 14,
            'Monthly' => 30,
            'Quarterly' => 90,
        ];

        $sortOrder = 0;

        foreach ($recurrences as $name => $intervalDays) {
            Recurrence::firstOrCreate(
                ['name' => $name],
                ['interval_days' => $intervalDays, 'is_active' => true, 'sort_order' => ++$sortOrder],
            );
        }
    }
}
