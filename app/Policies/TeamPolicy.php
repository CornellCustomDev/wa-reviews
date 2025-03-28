<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->teams()->exists();
    }

    public function view(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams)
            || $user->isTeamMember($team);
    }

    public function manageTeam(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams)
            || $user->isAbleTo(Permissions::ManageTeamMembers, $team->id);
    }

    public function create(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams);
    }

    public function update(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams)
            || $user->isAbleTo(Permissions::ManageTeamMembers, $team->id);
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams);
    }

    public function restore(User $user, Team $team): bool
    {
        return false;
    }

    public function forceDelete(User $user, Team $team): bool
    {
        return false;
    }
}
