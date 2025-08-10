<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'seller_id' => User::factory()->create(['role' => 'seller'])->id,
            'name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'industry' => $this->faker->randomElement(['Tech', 'Retail', 'Health']),
            'valuation' => $this->faker->numberBetween(50000, 5000000),
            'funding_goal' => $this->faker->numberBetween(10000, 1000000),
            'status' => 'active',
            'views_count' => $this->faker->numberBetween(0, 1000),
        ];
    }
}


