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
                'color_code'  => '#94a3b8',
                'description' => 'Entry-level job difficulty',
            ],
            [
                'name'        => 'Basic',
                'color_code'  => '#22c55e',
                'description' => 'Basic job requiring standard skills',
            ],
            [
                'name'        => 'Intermediate',
                'color_code'  => '#3b82f6',
                'description' => 'Intermediate complexity job',
            ],
            [
                'name'        => 'Advanced',
                'color_code'  => '#f59e0b',
                'description' => 'Advanced job requiring specialist skills',
            ],
            [
                'name'        => 'Expert',
                'color_code'  => '#ef4444',
                'description' => 'Expert-level job with high complexity',
            ],
            [
                'name'        => 'Special',
                'color_code'  => '#8b5cf6',
                'description' => 'Special job with unique requirements',
            ]
        ];

        foreach ($levels as $index => $level) {
            JobLevel::firstOrCreate(
                ['name' => $level['name']],
                [
                    'color_code'  => $level['color_code'],
                    'description' => $level['description'],
                    'is_active'   => true,
                    'sort_order'  => $index + 1,
                ],
            );
        }
    }
}
