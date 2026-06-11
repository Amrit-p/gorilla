<?php

namespace Database\Factories;

use App\Models\Discussion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discussion>
 */
class DiscussionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'category' => fake()->randomElement(['worker', 'budget', 'expansion', 'crm_update', 'general']),
            'worker_id' => null,
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'created_by' => User::factory(),
        ];
    }

    public function forWorker(User $worker): static
    {
        return $this->state(['worker_id' => $worker->id]);
    }
}
