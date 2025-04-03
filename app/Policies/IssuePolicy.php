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
        return $issue->project->team->isTeamMember($user)
            || $issue->project->isReportViewer($user);
    }

    public function create(User $user, Project $project): bool
    {
        return ($project->isInProgress() && $project->isProjectReviewer($user))
            || $user->isAbleTo(Permissions::ManageTeamProjects, $project->team);
    }

    public function update(User $user, Issue $issue): bool
    {
        return ($issue->project->isInProgress() && $issue->project->isProjectReviewer($user))
            || $user->isAbleTo(Permissions::ManageTeamProjects, $issue->project->team);
    }

    public function delete(User $user, Issue $issue): bool
    {
        return ($issue->project->isInProgress() && $issue->project->isProjectReviewer($user))
            || $user->isAbleTo(Permissions::ManageTeamProjects, $issue->project->team);
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
