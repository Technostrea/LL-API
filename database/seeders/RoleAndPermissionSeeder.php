<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Enums\RoleEnum;
use App\Enums\PermissionEnum;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionEnum::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        foreach (RoleEnum::all() as $role) {
            $roleInstance = Role::firstOrCreate(['name' => $role]);

            if ($role === RoleEnum::ADMIN->value) {
                $roleInstance->syncPermissions(PermissionEnum::all());
            } elseif ($role === RoleEnum::OWNER->value) {
                $roleInstance->syncPermissions([
                    PermissionEnum::VIEW_PROPERTIES->value,
                    PermissionEnum::CREATE_PROPERTIES->value,
                ]);
            } elseif ($role === RoleEnum::AGENCY->value) {
                $roleInstance->syncPermissions([
                    PermissionEnum::VIEW_PROPERTIES->value,
                    PermissionEnum::CREATE_PROPERTIES->value,
                    PermissionEnum::UPDATE_PROPERTIES->value,
                ]);
            } elseif ($role === RoleEnum::TENANT->value) {
                $roleInstance->syncPermissions([
                    PermissionEnum::VIEW_PROPERTIES->value,
                ]);
            }
        }
    }
}
