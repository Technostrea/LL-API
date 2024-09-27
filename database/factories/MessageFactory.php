<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sender_id' => 1,
            'receiver_id' => 2,
            'property_id' => 1,
            'content' => $this->faker->sentence,
            'is_read' => false,
            'read_at' => null,
        ];
    }

    public function read(): self
    {
        return $this->state([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
