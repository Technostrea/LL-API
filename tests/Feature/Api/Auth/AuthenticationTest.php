<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;


$route = 'api/v1/auth/login';

test('users can authenticate using the login route', function () {
    $user = User::factory()->create();

    $response = $this->postJson('api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertStatus(\Symfony\Component\HttpFoundation\Response::HTTP_OK)
             ->assertJsonStructure(['success', 'message', 'data' => ['token', 'user']]);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    Sanctum::actingAs(
        User::factory()->create()
    );

    $response = $this->post('/api/v1/auth/logout');

    $response->assertStatus(\Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
});
