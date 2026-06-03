<?php

namespace Database\Seeders;

use App\Models\JobLevel;
use Illuminate\Database\Seeder;

class JobLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'name'        => 'Entry',
                'description' => 'Entry-level job difficulty',
            ],
            [
                'name'        => 'Basic',
                'description' => 'Basic job requiring standard skills',
            ],
            [
                'name'        => 'Intermediate',
                'description' => 'Intermediate complexity job',
            ],
            [
                'name'        => 'Advanced',
                'description' => 'Advanced job requiring specialist skills',
            ],
            [
                'name'        => 'Expert',
                'description' => 'Expert-level job with high complexity',
            ],
        ];

        foreach ($levels as $index => $level) {
            JobLevel::firstOrCreate(
                ['name' => $level['name']],
                [
                    'description' => $level['description'],
                    'is_active'   => true,
                    'sort_order'  => $index + 1,
                ],
            );
        }
    }
}
