<?php

namespace Database\Seeders;

use App\Models\AccountingLevel;
use Illuminate\Database\Seeder;

class AccountingLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'name'        => "\u{2B50} C",
                'description' => 'Cash on spot',
            ],
            [
                'name'        => "\u{2B50} I",
                'description' => 'Payment after sending invoice',
            ],
            [
                'name'        => "\u{2B50} T",
                'description' => 'Payment after text message',
            ],
            [
                'name'        => "\u{2B50}\u{2B50} C",
                'description' => 'Cash payment for multiple jobs',
            ],
            [
                'name'        => "\u{2B50}\u{2B50} I",
                'description' => 'Invoicing',
            ],
            [
                'name'        => "\u{2B50}\u{2B50} T",
                'description' => '',
            ],
            [
                'name'        => "\u{2B50}\u{2B50} P",
                'description' => 'On the spot payment for multiple jobs',
            ],
        ];

        foreach ($levels as $index => $level) {
            AccountingLevel::firstOrCreate(
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
