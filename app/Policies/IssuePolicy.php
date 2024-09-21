<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Issue $issue): bool
    {
        return $user?->can('view', $issue->project) ?? true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user, Project $project): bool
    {
        return $user?->can('update', $project) ?? true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Issue $issue): bool
    {
        return $user?->can('update', $issue->project) ?? true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Issue $issue): bool
    {
        return $user?->can('update', $issue->project) ?? true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Issue $issue): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Issue $issue): bool
    {
        return false;
    }
}
