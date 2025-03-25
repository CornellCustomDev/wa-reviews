<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Project;
use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Issue $issue): bool
    {
        return $user->isTeamMember($issue->project->team);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->isAbleTo(Permissions::EditProjects, $project->team);
    }

    public function update(User $user, Issue $issue): bool
    {
        return $user->isAbleTo(Permissions::EditProjects, $issue->project->team);
    }

    public function delete(User $user, Issue $issue): bool
    {
        return $user->isAbleTo(Permissions::EditProjects, $issue->project->team);
    }

    public function restore(User $user, Issue $issue): bool
    {
        return false;
    }

    public function forceDelete(User $user, Issue $issue): bool
    {
        return false;
    }
}
