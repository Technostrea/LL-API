<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->text(),
            'price' => $this->faker->randomFloat(2, 1000, 100000),
            'area' => $this->faker->randomFloat(2, 50, 500),
            'status' => $this->faker->randomElement(['available', 'rented', 'sold']),
            'property_type' => $this->faker->randomElement(['house', 'apartment', 'commercial', 'land']),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->randomElement(['CA', 'NY', 'TX', 'FL', 'OH', 'PA', 'IL', 'WA', 'GA', 'NC', 'MI', 'NJ', 'VA', 'AZ', 'MA', 'TN', 'IN', 'MO', 'MD', 'WI', 'CO', 'MN', 'SC', 'AL', 'LA', 'KY', 'OR', 'OK', 'CT', 'IA', 'MS', 'AR', 'UT', 'NV', 'KS', 'NM', 'NE', 'WV', 'ID', 'HI', 'ME', 'NH', 'MT', 'RI', 'DE', 'SD', 'ND', 'AK', 'VT', 'WY']),
            'zip' => $this->faker->postcode(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
        ];
    }
}
