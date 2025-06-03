<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->teams()->exists();
    }

    public function view(User $user, Team $team): bool
    {
        return $team->isTeamMember($user)
            || $user->isAbleTo(Permissions::ManageTeams);
    }

    public function manageTeam(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamMembers, $team)
            || $user->isAbleTo(Permissions::ManageTeams);
    }

    public function create(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageTeams);
    }

    public function update(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamMembers, $team)
            || $user->isAbleTo(Permissions::ManageTeams);
    }

    public function delete(User $user, Team $team): bool
    {
        // If a team has projects, it cannot be deleted
        if ($team->projects()->exists()) {
            return false;
        }

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

    public function manageProjects(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamProjects, $team)
            || $user->isAbleTo(Permissions::ManageTeams);
    }

    public function createProjects(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::CreateTeamProjects, $team);
    }

    public function editProjects(User $user, Team $team): bool
    {
        return $user->isAbleTo(Permissions::EditProjects, $team);
    }
}
