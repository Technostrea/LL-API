<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PropertyFavorites>
 */
class PropertyFavoritesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => $this->faker->numberBetween(1, 100),
            'user_id' => $this->faker->numberBetween(1, 100),
        ];
    }
}
