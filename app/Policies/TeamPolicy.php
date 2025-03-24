<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams)
            || $user->isAbleTo(Permissions::ManageTeamMembers, $team->id);
    }

    public function manageTeam(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams)
            || $user->isAbleTo(Permissions::ManageTeamMembers, $team->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams)
            || $user->isAbleTo(Permissions::ManageTeamMembers, $team->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Team $team): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Team $team): bool
    {
        return false;
    }
}
