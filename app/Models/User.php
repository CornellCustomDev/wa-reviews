<?php

namespace App\Models;

use App\Enums\Roles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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

    public function isTeamMember(Team $team): bool
    {
        return $this->teams->contains($team);
    }

    public function getTeamRoles(Team $team): Collection
    {
        return $this->roles()->wherePivot('team_id', $team->id)->get();
    }
}
