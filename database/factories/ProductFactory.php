<?php

namespace Database\Factories;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->text(),
            'slug' => fake()->slug(),
            'availability_status' => fake()->randomElement(ProductStatusEnum::cases()),
            'sku' => fake()->unique()->bothify('SKU-########'),
            'description' => fake()->realText(2000),
            'price' => fake()->randomFloat(2, 2, 5),
            'dimensions' => [
                'width' => fake()->randomFloat(2, 5, 50),
                'height' => fake()->randomFloat(2, 5, 50),
                'depth' => fake()->randomFloat(2, 5, 50),
            ],
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
