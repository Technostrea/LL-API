<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agency>
 */
class AgencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'agency_name' => $this->faker->company,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'agency_license' => $this->faker->uuid,
            'address' => $this->faker->address,
            'logo' => $this->faker->imageUrl(),
            'description' => $this->faker->sentence,
            'website' => $this->faker->url,
        ];
    }
}
