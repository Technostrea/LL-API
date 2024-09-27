<?php

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
    $this->admin = User::factory()->create()->assignRole(RoleEnum::ADMIN->value);
    $this->user = User::factory()->create();
});

it('can list all permissions', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson(route('permissions.index'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'created_at', 'updated_at']
            ]
        ]);
});

it('can create a new permission', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson(route('permissions.store'), [
            'name' => 'edit properties'
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'name']
        ]);
});

//it('can show a specific permission', function () {
//    $admin = User::factory()->create()->assignRole('admin');
//    $permission = Permission::create(['name' => 'edit properties']);
//
//    $response = $this->actingAs($admin)->getJson("/api/permissions/{$permission->id}");
//
//    $response->assertStatus(200)
//        ->assertJsonStructure([
//            'data' => ['id', 'name']
//        ]);
//});
//
//it('can update a permission', function () {
//    $admin = User::factory()->create()->assignRole('admin');
//    $permission = Permission::create(['name' => 'edit properties']);
//
//    $response = actingAs($admin)->putJson("/api/permissions/{$permission->id}", [
//        'name' => 'manage properties'
//    ]);
//
//    $response->assertStatus(200)
//        ->assertJson([
//            'message' => 'Permission updated successfully',
//            'data' => ['name' => 'manage properties']
//        ]);
//});
//
//it('can delete a permission', function () {
//    $admin = User::factory()->create()->assignRole('admin');
//    $permission = Permission::create(['name' => 'edit properties']);
//
//    $response = actingAs($admin)->deleteJson("/api/permissions/{$permission->id}");
//
//    $response->assertStatus(200)
//        ->assertJson(['message' => 'Permission deleted successfully']);
//});
