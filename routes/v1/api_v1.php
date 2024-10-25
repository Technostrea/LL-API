<?php

use App\Http\Controllers\api\AgencyController;
use App\Http\Controllers\api\MessageController;
use App\Http\Controllers\api\PermissionController;
use App\Http\Controllers\api\PropertyController;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\UserController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => 'v1'
], function () {

    Route::middleware(
        app()->environment(['local', 'dev']) ?
            [] :
            ['auth:sanctum', 'verified']
    )->group(function () {

        // Users route
        Route::middleware(
            app()->environment(['local', 'dev']) ?
                [] :
                ['role:admin']
        )->group(function () {
            Route::get('/users', [UserController::class, 'index'])
                ->name('users.index');
            Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole'])
                ->name('users.assign-role');
            Route::post('/users/{id}/revoke-role', [UserController::class, 'revokeRole'])
                ->name('users.revoke-role');
            Route::get('/users/{id}', [UserController::class, 'show'])
                ->name('users.show');
            Route::put('/users/{id}', [UserController::class, 'update'])
                ->name('users.update');
            Route::delete('/users/{id}', [UserController::class, 'destroy'])
                ->name('users.destroy');
        });
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/me', [UserController::class, 'me']);

        // Rôles routes
        Route::get('roles', [RoleController::class, 'index'])
            ->middleware('role:admin')
            ->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])
            ->middleware('role:admin')
            ->name('roles.store');
        Route::get('roles/{id}', [RoleController::class, 'show'])
            ->middleware('role:admin')
            ->name('roles.show');
        Route::put('roles/{id}', [RoleController::class, 'update'])
            ->middleware('role:admin')
            ->name('roles.update');
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('roles.destroy');
        Route::get('roles/{id}/permissions', [RoleController::class, 'permissions'])
            ->middleware('role:admin')
            ->name('roles.permissions');
        Route::post('roles/{id}/permissions', [RoleController::class, 'addPermission'])
            ->middleware('role:admin')
            ->name('roles.add-permission');

        // Permissions routes
        Route::get('/permissions', [PermissionController::class, 'index'])
            ->middleware('role:admin')
            ->name('permissions.index');
        Route::post('/permissions', [PermissionController::class, 'store'])
            ->middleware('role:admin')
            ->name('permissions.store');
        Route::get('/permissions/{id}', [PermissionController::class, 'show'])
            ->middleware('role:admin')
            ->name('permissions.show');
        Route::put('/permissions/{id}', [PermissionController::class, 'update'])
            ->middleware('role:admin')
            ->name('permissions.update');
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('permissions.destroy');

        // Private properties routes
        Route::get('/properties/me', [PropertyController::class, 'myProperties'])
            ->name('properties.me');
        Route::post('/properties', [PropertyController::class, 'store'])
            ->name('properties.store');
        Route::get('/properties/{id}', [PropertyController::class, 'show'])
            ->name('properties.show');
        Route::put('/properties/{id}', [PropertyController::class, 'update'])
            ->name('properties.update');
        Route::delete('/properties/{id}', [PropertyController::class, 'destroy'])
            ->name('properties.destroy');

        // Property Images routes
        Route::post('/properties/{id}/images', [PropertyController::class, 'storeImage'])
            ->middleware('role:owner')
            ->name('properties.images.store');
        Route::delete('/properties/{id}/images/{imageId}', [PropertyController::class, 'destroyImage'])
            ->middleware('role:owner')
            ->name('properties.images.destroy');

        // Favorites routes
        Route::get('/favorites', [PropertyController::class, 'favorites'])
            ->name('favorites.index');
        Route::post('/favorites/{property_id}', [PropertyController::class, 'addFavorite'])
            ->name('favorites.store');
        Route::delete('/favorites/{property_id}', [PropertyController::class, 'removeFavorite'])
            ->name('favorites.destroy');

        // Messages routes
        Route::get('/messages', [MessageController::class, 'index'])
            ->name('messages.index');
        // Récupérer les conversations de l'utilisateur connecté
        Route::post('/messages', [MessageController::class, 'store'])
            ->name('messages.store');
        // Envoyer un nouveau message
        Route::get('/messages/{id}', [MessageController::class, 'show'])
            ->name('messages.show');
        // Récupérer les messages d'une conversation
        Route::put('/messages/{id}', [MessageController::class, 'update'])
            ->name('messages.update');
        // Marquer un message comme lu
        Route::delete('/messages/{id}', [MessageController::class, 'destroy'])
            ->name('messages.destroy');
        // Supprimer une conversation

        // Agencies routes
        Route::middleware(
            app()->environment(['local', 'dev']) ?
                [] :
                ['role:admin']
        )->group(function () {
            Route::get('/agencies', [AgencyController::class, 'index'])
                ->name('agencies.index');
            Route::post('/agencies', [AgencyController::class, 'store'])
                ->middleware('role:admin')
                ->name('agencies.store');
            Route::get('/agencies/{id}', [AgencyController::class, 'show'])
                ->middleware('role:admin')
                ->name('agencies.show');
            Route::put('/agencies/{id}', [AgencyController::class, 'update'])
                ->middleware('role:admin')
                ->name('agencies.update');
            Route::delete('/agencies/{id}', [AgencyController::class, 'destroy'])
                ->middleware('role:admin')
                ->name('agencies.destroy');
        });
    });

    // Public properties routes
    Route::get('/properties', [PropertyController::class, 'index'])
        ->name('properties.index');

    require __DIR__ . '/auth.php';
});
