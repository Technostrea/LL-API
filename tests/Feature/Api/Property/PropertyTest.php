<?php

use App\Models\PropertyImages;
use App\Models\User;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class)->beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
    $this->user = User::factory()->create();
    $this->owner = User::factory()->create()->assignRole('owner');
    $this->admin = User::factory()->create()->assignRole('admin');
    $this->property = Property::factory()->create();
    Storage::fake('s3');
});

it('can create a property', function () {
    $owner = User::factory()->create()->assignRole('owner');

    $propertyData = [
        'title' => 'My New Property',
        'description' => 'A beautiful property description.',
        'price' => 300000,
        'area' => 120,
        'status' => 'available',
        'property_type' => 'house',
        'address' => '123 Street Name',
        'city' => 'CityName',
        'state' => 'StateName',
        'zip' => '12345',
        'latitude' => 48.8566,
        'longitude' => 2.3522,
    ];

    $response = $this->actingAs($owner, 'sanctum')
        ->postJson(route('properties.store'), $propertyData);

    $response->assertStatus(201)
        ->assertJsonFragment($propertyData);

    $this->assertDatabaseHas('properties', $propertyData);
});
it('validates property creation data', function () {
    $propertyData = [
        'description' => 'A beautiful property description.',
        'price' => 300000,
        'area' => 120,
        'status' => 'available',
        'property_type' => 'house',
        'address' => '123 Street Name',
        'city' => 'CityName',
        'state' => 'StateName',
        'zip' => '12345',
        'latitude' => 48.8566,
        'longitude' => 2.3522,
    ];

    $response = $this->actingAs($this->owner, 'sanctum')
        ->postJson(route('properties.store'), $propertyData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors('title');
});
it('can show a property', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson(route('properties.show', $property->id));

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment([
            'title' => $property->title,
            'description' => $property->description,
        ]);
});
it('can update a property', function () {
    $owner = User::factory()->create()->assignRole('owner');
    $property = Property::factory()->create(['user_id' => $owner->id]);

    $updatedData = [
        'title' => 'Updated Property Title',
        'description' => 'Updated description.',
    ];

    $response = $this->actingAs($owner, 'sanctum')
        ->putJson(route('properties.update', $property->id), $updatedData);

    $response->assertStatus(200)
        ->assertJsonFragment($updatedData);

    $this->assertDatabaseHas('properties', $updatedData);
});
it('can delete a property', function () {
    $owner = User::factory()->create()->assignRole('owner');
    $property = Property::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($owner, 'sanctum')
        ->deleteJson(route('properties.destroy', $property->id));

    $response->assertStatus(200)
        ->assertJson(['message' => 'Property deleted successfully']);

    // Vérifier que la propriété a bien été supprimée de la base de données
    $this->assertDatabaseMissing('properties', ['id' => $property->id]);
});
it('can show user properties', function () {
    $user = User::factory()->create();
    $properties = Property::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson(route('properties.me'));

    $response->assertStatus(200)
        ->assertJsonFragment(['title' => $properties[0]->title])
        ->assertJsonFragment(['title' => $properties[1]->title])
        ->assertJsonFragment(['title' => $properties[2]->title]);
});

it('can store an image for a property', function () {
    // Simuler le disque S3 pour éviter de stocker réellement des fichiers lors des tests
    Storage::fake('s3');

    // Créer un utilisateur avec le rôle propriétaire
    $user = User::factory()->create()->assignRole('owner');

    // Créer une propriété liée à cet utilisateur
    $property = Property::factory()->create(['user_id' => $user->id]);

    // Générer une image simulée
    $image = UploadedFile::fake()->image('property.jpg');

    // Exécuter la requête de stockage d'image
    $response = $this->actingAs($user, 'sanctum')
        ->postJson(route('properties.images.store', $property->id), [
            'image' => $image,
        ]);

    // Vérifier que la réponse a bien un statut HTTP 201 (créé)
    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJson(['message' => 'Image uploaded successfully']);

    // Récupérer le nom de fichier utilisé pour l'upload
    $imageName = time() . '_' . trim(str_replace(" ", "_", $image->getClientOriginalName()));
    // Vérifier que l'image a bien été stockée sur le disque S3
    Storage::disk('s3')->assertExists('images/' . $imageName);

    // Vérifier que la base de données contient bien l'enregistrement pour l'image de la propriété
    $this->assertDatabaseHas('property_images', [
        'property_id' => $property->id,
        'image_url' => 'images/' . $imageName,
    ]);
});
it('deletes an image successfully', function () {
    $user = User::factory()->create()->assignRole('owner');
    $property = Property::factory()->create(['user_id' => $user->id]);
    $image = PropertyImages::factory()->create(['property_id' => $property->id]);

    Auth::login($user);

    $response = $this->deleteJson(
        route('properties.images.destroy',
            ['id' => $property->id, 'imageId' => $image->id]
        ));

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Image deleted successfully',
        ]);

    $this->assertDatabaseMissing('property_images', ['id' => $image->id]);
});
//it('returns 404 if property not found', function () {
//    $response = $this->actingAs($this->admin, 'sanctum')
//        ->deleteJson(route('properties.images.destroy',
//            ['id' => 999, 'imageId' => 999]
//        ));
//
//    $response->assertStatus(404)
//        ->assertJsonStructure([
//            'message',
//        ]);
//});
it('returns 401 if unauthorized', function () {
    $user = User::factory()->create()->assignRole('owner');
    $property = Property::factory()->create();
    $image = PropertyImages::factory()->create(['property_id' => $property->id]);

    Auth::login($user);

    $response = $this->deleteJson(route('properties.images.destroy',
        ['id' => $property->id, 'imageId' => $image->id]
    ));

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJson([
            'message' => 'Unauthorized',
        ]);
});
it('returns 404 if image not found', function () {
    $user = User::factory()->create()->assignRole('owner');
    $property = Property::factory()->create(['user_id' => $user->id]);

    Auth::login($user);

    $response = $this->deleteJson(route('properties.images.destroy',
        ['id' => $property->id, 'imageId' => 999]));

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Image not found',
        ]);
});


it('can add a property to favorites', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create();

    Auth::login($user);

    $response = $this->actingAs($user)
        ->postJson(route('favorites.store', $property->id));

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Property added to favorites successfully.',
        ]);

    expect($user->favorites()->where('property_id', $property->id)->exists())->toBeTrue();
});
it('cannot add the same property to favorites twice', function () {
    $this->user->favorites()->attach($this->property->id);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('favorites.store', $this->property->id));

    $response->assertStatus(Response::HTTP_CONFLICT)
        ->assertJson([
            'message' => 'Property is already in favorites.',
        ]);
});
it('returns error if the property does not exist when adding to favorites', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('favorites.store', 999));

    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJsonStructure(['message']);
});
it('can remove a property from favorites', function () {
    $this->user->favorites()->attach($this->property->id);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('favorites.destroy', $this->property->id));

    $response->assertStatus(200)
        ->assertJsonStructure(['message']);

    expect($this->user->favorites()->where('property_id', $this->property->id)->exists())->toBeFalse();
});
it('returns error if the property is not in favorites when removing', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('favorites.destroy', $this->property->id));

    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJsonStructure(['message']);
});
it('returns error if the property does not exist when removing from favorites', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('favorites.destroy', 999));

    $response->assertStatus(404)
        ->assertJsonStructure(['message']);
});
