<?php

namespace Database\Factories;

use App\Models\Discussion;
use App\Models\DiscussionSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscussionSection>
 */
class DiscussionSectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'discussion_id' => Discussion::factory(),
            'sort_order' => fake()->numberBetween(0, 10),
            'heading' => fake()->sentence(3),
            'body' => fake()->optional()->paragraph(),
        ];
    }
}
