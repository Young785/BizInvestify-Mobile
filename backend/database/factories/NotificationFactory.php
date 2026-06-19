<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'info',
            'title' => fake()->sentence(3),
            'message' => fake()->sentence(),
            'data' => [],
            'is_read' => false,
            'priority' => 'medium',
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn () => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }
}
