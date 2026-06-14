<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total_price' => fake()->randomFloat(2, 10, 1000),
            'status' => fake()->randomElement(OrderStatusEnum::cases()),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
