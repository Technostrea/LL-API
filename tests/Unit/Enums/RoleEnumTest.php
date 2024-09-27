<?php

use App\Enums\RoleEnum;

it('returns all role enum values', function () {
    $roles = RoleEnum::all();
    expect($roles)->toBe([
        'admin',
        'owner',
        'agency',
        'tenant',
    ]);
});

it('has the correct individual enum values', function () {
    expect(RoleEnum::ADMIN->value)->toBe('admin')
        ->and(RoleEnum::OWNER->value)->toBe('owner')
        ->and(RoleEnum::AGENCY->value)->toBe('agency')
        ->and(RoleEnum::TENANT->value)->toBe('tenant');
});
