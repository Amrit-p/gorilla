<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientDocument>
 */
class ClientDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->slug().'.pdf';

        return [
            'client_id' => Client::factory(),
            'original_name' => $name,
            'file_path' => 'client-documents/'.$this->faker->uuid().'/'.$name,
            'file_size' => $this->faker->numberBetween(1024, 5_242_880),
            'file_type' => 'pdf',
            'uploaded_by' => User::factory(),
        ];
    }
}
