<?php

namespace Tests\Feature;

use App\Models\Van;
use Database\Seeders\VanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VanSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_two_active_vans(): void
    {
        $this->seed(VanSeeder::class);

        $this->assertSame(['Van 1', 'Van 2'], Van::query()->active()->ordered()->pluck('name')->all());
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(VanSeeder::class);
        $this->seed(VanSeeder::class);

        $this->assertSame(2, Van::query()->count());
    }
}
