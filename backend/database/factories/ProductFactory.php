<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'seller_id' => User::factory()->create(['role' => 'seller'])->id,
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 5000),
            'currency' => 'USD',
            'category' => $this->faker->randomElement(['Tech', 'Retail', 'Health']),
            'tags' => [$this->faker->word(), $this->faker->word()],
            'images' => [],
            'status' => 'active',
            'inventory_count' => $this->faker->numberBetween(0, 100),
            'views_count' => $this->faker->numberBetween(0, 1000),
        ];
    }
}


