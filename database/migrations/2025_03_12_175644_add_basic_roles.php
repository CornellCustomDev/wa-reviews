<?php

use App\Enums\Permissions;
use App\Enums\Roles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    const array ROLE_PERMISSIONS = [
        Roles::SuperAdmin->value => [
            Permissions::ManageSiteConfig->value,
            Permissions::ManageUsers->value,
            Permissions::ManageProjects->value,
        ],
        Roles::ProjectManager->value => [
            Permissions::ManageProjects->value,
        ]
    ];

    public function up(): void
    {
        foreach (App\Enums\Permissions::values() as $permission) {
            try {
                Permission::create(['name' => $permission]);
            } catch (PermissionAlreadyExists) {
                //
            }
        }

        foreach (App\Enums\Roles::values() as $roleName) {
            try {
                $role = Role::create(['name' => $roleName]);
            } catch (RoleAlreadyExists) {
                $role = Role::findByName($roleName);
            }
            $role->syncPermissions(self::ROLE_PERMISSIONS[$roleName] ?? []);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $roles = Role::all();
        foreach ($roles as $role) {
            $role->syncPermissions([]);
            $role->delete();
        }

        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            $permission->delete();
        }
    }
};
