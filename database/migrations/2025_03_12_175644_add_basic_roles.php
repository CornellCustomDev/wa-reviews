<?php

use App\Enums\Permissions;
use App\Enums\Roles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laratrust\Models\Permission;
use Laratrust\Models\Role;

return new class extends Migration
{
    const array ROLE_PERMISSIONS = [
        Roles::SiteAdmin->value => [
            Permissions::ManageSiteConfig->value,
            Permissions::ManageTeams->value,
            Permissions::ManageTeamMembers->value,
            Permissions::ManageTeamProjects->value,
            Permissions::EditProjects->value,
        ],
        Roles::TeamAdmin->value => [
            Permissions::ManageTeamMembers->value,
            Permissions::ManageTeamProjects->value,
            Permissions::EditProjects->value,
        ],
        Roles::Reviewer->value => [
            Permissions::EditProjects->value,
        ],
    ];

    public function up(): void
    {
        foreach (App\Enums\Permissions::values() as $permission) {
            Permission::create([
                'name' => $permission,
                'display_name' => ucwords($permission),
            ]);
        }

        foreach (App\Enums\Roles::values() as $roleName) {
            $role = Role::create([
                'name' => $roleName,
                'display_name' => ucwords($roleName),
            ]);
            $permissions = Permission::whereIn('name', self::ROLE_PERMISSIONS[$roleName] ?? [])->get();
            $role->syncPermissions($permissions ?? []);
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
