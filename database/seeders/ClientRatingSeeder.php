<?php

namespace Database\Seeders;

use App\Models\ClientRating;
use Illuminate\Database\Seeder;

class ClientRatingSeeder extends Seeder
{
    public function run(): void
    {
        $ratings = [
            [
                'name' => 'Excellent',
                'description' => 'Reliable client, always pays on time.',
            ],
            [
                'name' => 'Good',
                'description' => 'Dependable client with minor issues.',
            ],
            [
                'name' => 'Average',
                'description' => 'Occasional delays or concerns.',
            ],
            [
                'name' => 'Poor',
                'description' => 'Frequent issues, handle with caution.',
            ],
        ];

        foreach ($ratings as $index => $rating) {
            ClientRating::firstOrCreate(
                ['name' => $rating['name']],
                [
                    'description' => $rating['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
