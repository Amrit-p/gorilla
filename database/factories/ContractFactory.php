<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Contractor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'contractor_id' => Contractor::factory(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => fake()->boolean(60) ? fake()->dateTimeBetween($start, '+2 years')->format('Y-m-d') : null,
            'status' => ContractStatus::Active->value,
        ];
    }
}
