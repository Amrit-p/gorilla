<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\ContractDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractDocument>
 */
class ContractDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'original_name' => fake()->word().'.pdf',
            'file_path' => 'contract-documents/1/'.fake()->uuid().'.pdf',
            'file_size' => fake()->numberBetween(1024, 1048576),
            'file_type' => 'pdf',
            'uploaded_by' => null,
        ];
    }
}
