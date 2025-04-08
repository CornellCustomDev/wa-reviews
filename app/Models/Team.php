<?php

namespace App\Models;

use App\Enums\Roles;
use App\Events\UserChanged;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Laratrust\Models\Team as LaratrustTeam;

class Team extends LaratrustTeam
{
//    public $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function addUser(User $user): void
    {
        $this->users()->attach($user);

        event(new UserChanged($user, $this, 'added'));
    }

    public function removeUser(User $user): void
    {
        // Remove any roles the user has on the team
        $user->syncRoles([], $this->id);
        // Remove the user from the team
        $this->users()->detach($user);

        event(new UserChanged($user, $this, 'removed'));
    }

    public function setUserRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles, $this->id);

        event(new UserChanged($user, $this, 'roles updated', [
            'user_name' => $user->name,
            'roles' => Role::find($this->roles)->pluck('display_name')->join(', '),
        ]));
    }

    public function getUserRoles(User $user): Collection
    {
        return $user->roles()->wherePivot('team_id', $this->id)->get();
    }

    public function isTeamMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function isTeamAdmin(User $user): bool
    {
        return $user->hasRole(Roles::TeamAdmin, $this->id);
    }

    public function isReviewer(User $user): bool
    {
        return $user->hasRole(Roles::Reviewer, $this->id);
    }
}
