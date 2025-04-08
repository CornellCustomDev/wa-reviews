<?php

namespace App\Models;

use App\Enums\Roles;
use CornellCustomDev\LaravelStarterKit\Ldap\LdapData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    use HasFactory, Notifiable;
    use HasRolesAndPermissions;

    protected $fillable = [
        'name',
        'email',
        'uid',
    ];

    // These two fields are not in use, but still do not include them in outputs
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(
            related: ProjectAssignment::class,
            foreignKey: 'user_id',
            localKey: 'id'
        )->with([
            'project:id,name,team_id',
            'project.team:id,name',
        ]);
    }

    public function assignedProjects(): HasManyThrough
    {
        return $this->throughAssignments()->hasProject();
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(Roles::SiteAdmin);
    }

    public function getTeams(): Collection
    {
        if ($this->isAdministrator()) {
            return Team::all();
        }

        return $this->teams;
    }

    public function getManagedTeams(): Collection
    {
        if ($this->isAdministrator()) {
            return Team::all();
        }

        $teamAdminRole = Role::firstWhere('name', Roles::TeamAdmin);

        // Get the teams where the user has the TeamAdmin role
        $teamIds = $this->roles()
            ->wherePivot('role_id', $teamAdminRole->id)
            ->get()
            ->pluck('pivot.team_id');

        return Team::whereIn('id', $teamIds)->get();
    }

    public static function createUserFromLdapData(LdapData $ldapData): User
    {
        $user = new User;
        $user->name = $ldapData->name();
        $user->email = $ldapData->email();
        $user->uid = $ldapData->principalName();
        $user->password = Hash::make('password');
        $user->save();

        Log::info("User::createUserFromLdapData: Created user $user->email with ID $user->id.");

        return $user;
    }
}
