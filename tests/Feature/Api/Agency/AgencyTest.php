<?php

use App\Models\Property;
use App\Models\User;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(RefreshDatabase::class)->beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

    $this->user = User::factory()->create();
    $this->owner = User::factory()->create()->assignRole('owner');
    $this->admin = User::factory()->create()->assignRole('admin');
    $this->user_agency = User::factory()->create()->assignRole('agency');

    $this->agency = Agency::factory()->create();
    $this->property = Property::factory()->create([
        'agency_id' => $this->agency->id
    ]);
})->afterEach(function () {
    Role::where('name', 'owner')->delete();
    Role::where('name', 'admin')->delete();
    Role::where('name', 'agency')->delete();

    User::where('email', $this->user->email)->delete();
});

it('can list all agencies', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson(route('agencies.index'));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'agency_name',
                    'phone',
                    'email',
                    'agency_license',
                    'address',
                    'logo',
                    'description',
                    'website',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);
});

it('can create a new agency', function () {
    $agencyData = [
        'user_id' => $this->admin->id,
        'agency_name' => 'Test Agency',
        'phone' => '1234567890',
        'email' => 'test@example.com',
        'agency_license' => 'LICENSE123',
        'address' => 'Test City',
        'logo' => 'test.jpg',
        'description' => 'Test description',
        'website' => 'https://test.com'
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson(route('agencies.store'), $agencyData);

    $response->assertStatus(ResponseAlias::HTTP_CREATED)
        ->assertJson([
            'message' => 'Agency created successfully.',
            'data' => [
                'user_id' => $this->admin->id,
                'agency_name' => 'Test Agency',
                'phone' => '1234567890',
                'email' => 'test@example.com',
                'agency_license' => 'LICENSE123',
                'address' => 'Test City',
                'logo' => 'test.jpg',
                'description' => 'Test description',
                'website' => 'https://test.com'
            ]
        ]);

    $this->assertDatabaseHas('agencies', ['agency_name' => 'Test Agency']);
});

it('can show a specific agency', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson(route('agencies.show', $this->agency->id));

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Agency retrieved successfully.',
            'data' => [
                'id' => $this->agency->id,
                'agency_name' => $this->agency->agency_name
            ]
        ]);
});

it('returns error if agency is not found when showing', function () {
    $response = $this->actingAs($this->admin)
        ->getJson(route('agencies.show', 999));

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Agency not found.'
        ]);
});

it('can update an agency', function () {
    $updateData = [
        'agency_name' => 'Updated Agency',
        'agency_license' => 'NEWLICENSE',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson(route('agencies.update', $this->agency->id), $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Agency updated successfully.',
            'data' => [
                'agency_name' => 'Updated Agency',
                'agency_license' => 'NEWLICENSE'
            ]
        ]);

    // Vérifie que les changements sont bien reflétés dans la base de données
    $this->assertDatabaseHas('agencies', ['agency_name' => 'Updated Agency']);
});

it('returns error if agency is not found when updating', function () {
    $updateData = [
        'agency_name' => 'Updated Agency',
        'agency_license' => 'NEWLICENSE',
    ];

    $response = $this->actingAs($this->admin)->putJson('/api/v1/agencies/999', $updateData);

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Agency not found.'
        ]);
});

it('can delete an agency', function () {
    $response = $this->actingAs($this->admin)->deleteJson("/api/v1/agencies/{$this->agency->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Agency deleted successfully.'
        ]);

    // Vérifie que l'agence a bien été supprimée de la base de données
    $this->assertDatabaseMissing('agencies', ['id' => $this->agency->id]);
});

it('returns error if agency is not found when deleting', function () {
    $response = $this->actingAs($this->admin)->deleteJson('/api/v1/agencies/999');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Agency not found.'
        ]);
});
