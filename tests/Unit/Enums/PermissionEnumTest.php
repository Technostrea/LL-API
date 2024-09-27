<?php

use App\Enums\PermissionEnum;

it('returns all permission enum values', function () {
    $permissions = PermissionEnum::all();

    expect($permissions)->toBe([
        'view-properties',
        'create-properties',
        'update-properties',
        'delete-properties',
    ]);
});

it('has the correct individual enum values', function () {
    expect(PermissionEnum::VIEW_PROPERTIES->value)->toBe('view-properties')
        ->and(PermissionEnum::CREATE_PROPERTIES->value)->toBe('create-properties')
        ->and(PermissionEnum::UPDATE_PROPERTIES->value)->toBe('update-properties')
        ->and(PermissionEnum::DELETE_PROPERTIES->value)->toBe('delete-properties');
});
