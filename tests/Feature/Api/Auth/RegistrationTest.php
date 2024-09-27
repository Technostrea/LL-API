<?php

test('new users can register', function () {
    $response = $this->post('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'message',
        'data' => [
            'user' => [
                'name',
                'email',
            ],
            'token'
        ]
    ]);
});
