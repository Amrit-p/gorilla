<?php

namespace Database\Seeders;

use App\Models\Recurrence;
use Illuminate\Database\Seeder;

class RecurrenceSeeder extends Seeder
{
    public function run(): void
    {
        $recurrences = collect([
            'One-Time',
            'Daily',
            'Weekly',
            'Bi-Weekly',
            'Monthly',
            'Quarterly',
        ])->values()->map(fn (string $name, int $i) => [
            'name'       => $name,
            'is_active'  => true,
            'sort_order' => $i + 1,
        ]);

        Recurrence::insert($recurrences->all());
    }
}
