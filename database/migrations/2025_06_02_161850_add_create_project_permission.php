<?php

use App\Enums\Permissions;
use App\Enums\Roles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laratrust\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $create = Permissions::CreateTeamProjects;
        Permission::updateOrCreate(
            ['name' => $create->value],
            ['display_name' => ucwords($create->value)]
        );

        foreach (Laratrust\Models\Role::all() as $role) {
            $permissions = Permission::whereIn('name', Roles::getRolePermissions($role->name))->get();
            $role->syncPermissions($permissions ?? []);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::where('name', Permissions::CreateTeamProjects->value)->first();

        foreach (Laratrust\Models\Role::all() as $role) {
            $role->removePermission($permission);
        }
        $permission->delete();
    }
};
