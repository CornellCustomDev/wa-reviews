<?php

namespace App\Models;

use App\Enums\Roles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    use HasFactory, Notifiable;
    use HasRolesAndPermissions;

    protected $fillable = [
        'name',
        'email',
    ];

    // These two fields are not in use, but do not include them in outputs
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
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
        $teamIds = $this->roles()
            ->wherePivot('role_id', $teamAdminRole->id)
            ->get()
            ->pluck('pivot.team_id');

        return Team::whereIn('id', $teamIds)->get();
    }

    public function isTeamMember(Team $team): bool
    {
        return $this->teams->contains($team);
    }

    public function getTeamRoles(Team $team): Collection
    {
        return $this->roles()->wherePivot('team_id', $team->id)->get();
    }
}
