<?php

namespace Tests\Feature\Api\Users;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


uses(RefreshDatabase::class)->beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
    $this->admin = User::factory()->create()->assignRole(RoleEnum::ADMIN->value);
    $this->user = User::factory()->create();
});

it('retrieves all users successfully if admin', function () {
    User::factory()->count(5)->create();

    $response = $this->actingAs($this->admin, 'sanctum')->getJson(route('users.index'));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure(['data', 'message']);
});
it('refuses to retrieve all users if not admin', function () {
    User::factory()->count(5)->create();

    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('users.index'));

    $response->assertStatus(ResponseAlias::HTTP_FORBIDDEN)
        ->assertJsonFragment(['message' => 'You are not authorized to access this resource.']);
});
it('retrieves users with filters', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    $response = $this->actingAs($this->admin, 'sanctum')->getJson(route('users.index', ['name' => 'John', 'email' => 'john@example.com']));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonFragment(['name' => 'John Doe', 'email' => 'john@example.com']);
});
it('retrieves user by id successfully', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin, 'sanctum')->getJson(route('users.show', $user->id));
    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonFragment(['id' => $user->id]);
});
it('returns 404 if user not found', function () {
    $response = $this->actingAs($this->admin, 'sanctum')->getJson(route('users.show', 999));

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND)
        ->assertJsonFragment(['message' => 'User not found.']);
});
it('updates user successfully', function () {
    $user = User::factory()->create();

    $data = ['name' => 'Updated Name', 'email' => 'updated@example.com'];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson(route('users.update', $user->id), $data);

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonFragment($data);
});
it('returns 404 if updating non existent user', function () {
    $data = ['name' => 'Updated Name', 'email' => 'updated@example.com'];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson(route('users.update', 999), $data);

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND)
        ->assertJsonFragment(['message' => 'User not found.']);
});
it('deletes user successfully by admin', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson(route('users.destroy', $user->id));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonFragment(['message' => 'User deleted successfully.']);
});
it('returns 404 if deleting non existent user', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson(route('users.destroy', 999));

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND)
        ->assertJsonFragment(['message' => 'User not found.']);
});
it('can assign a role to a user', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson(route('users.assign-role', $this->user->id), [
            'role' => 'owner'
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Role assigned successfully.',
        ]);

    expect($this->user->hasRole('owner'))->toBeTrue();
});
it('returns error when assigning a non-existent role', function () {

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson(route('users.assign-role', $this->user->id), [
            'role' => 'non-existent-role'
        ]);

    $response->assertStatus(400)
        ->assertJsonStructure(['errors']);
});
it('can revoke a role from a user', function () {
    $this->user->assignRole('owner');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson(route('users.revoke-role', $this->user->id), [
            'role' => 'owner'
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Role revoked successfully.',
        ]);

    expect($this->user->hasRole('owner'))->toBeFalse();
});
it('returns error when revoking a non-existent role', function () {
    $response = $this->actingAs($this->admin,'sanctum')
        ->postJson(route('users.revoke-role', $this->user->id), [
            'role' => 'non-existent-role'
        ]);

    $response->assertStatus(400)
        ->assertJsonStructure(['errors']);
});
