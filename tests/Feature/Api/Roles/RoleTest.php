<?php


use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


uses(RefreshDatabase::class)->beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
    $this->admin = User::factory()->create()->assignRole(RoleEnum::ADMIN->value);
    $this->user = User::factory()->create();
});

it('can list all roles', function () {

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson(route('roles.index'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'created_at', 'updated_at']
            ]
        ]);
});

it('can create a new role', function () {

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson(route('roles.store'), [
            'name' => 'manager'
        ]);

    $response->assertStatus(ResponseAlias::HTTP_CREATED)
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'name']
        ]);
});

it('can show a specific role', function () {

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson(route('roles.show', Role::first()->id));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'data' => ['id', 'name']
        ]);
});

it('can update a role', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson(route('roles.update', Role::first()->id), [
            'name' => 'new_owner'
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'name']
        ]);
});

it('can delete a role', function () {

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson(route('roles.destroy', Role::first()->id));

    $response->assertStatus(200)
        ->assertExactJsonStructure([
            'message',
            'data',
            'success'
        ]);
});

it('can retrieve permissions for a specific role', function () {

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson(route('roles.permissions', Role::first()->id));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name']
            ]
        ]);
});

it('can add permission to a role', function () {

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson(route('roles.add-permission', Role::first()->id), [
            'permission_name' => PermissionEnum::VIEW_PROPERTIES->value
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                '*' => ['id', 'name']
            ]
        ]);
});

