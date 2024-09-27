<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            [
                'sender_id' => 1,
                'receiver_id' => 2,
                'property_id' => 1,
                'content' => 'Hello, I am interested in your property.',
                'is_read' => false,
                'read_at' => null,
            ],
            [
                'sender_id' => 2,
                'receiver_id' => 1,
                'property_id' => 1,
                'content' => 'Hello, I am interested in your property.',
                'is_read' => false,
                'read_at' => null,
            ],
            [
                'sender_id' => 1,
                'receiver_id' => 2,
                'property_id' => 2,
                'content' => 'Hello, I am interested in your property.',
                'is_read' => false,
                'read_at' => null,
            ],
            [
                'sender_id' => 2,
                'receiver_id' => 1,
                'property_id' => 2,
                'content' => 'Hello, I am interested in your property.',
                'is_read' => false,
                'read_at' => null,
            ],
        ];

        foreach ($messages as $message) {
            \App\Models\Message::create($message);
        }
    }
}
