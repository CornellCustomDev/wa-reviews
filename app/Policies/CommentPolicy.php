<?php

namespace App\Policies;

use App\Enums\ProjectStatus;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Scope;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Scope|Issue $commentable): bool
    {
        $project = $commentable->project;

        if (! in_array($project->status, ProjectStatus::reviewedCases())) {
            return false;
        }

        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
            || $project->isVerifier($user)
            || $user->can('manage-projects', $project->team)
            || $project->isReportViewer($user);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->isOwnComment($user)
            && now() <= $comment->created_at->addMinutes(10);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->isOwnComment($user) || $user->isAdministrator();
    }
}
