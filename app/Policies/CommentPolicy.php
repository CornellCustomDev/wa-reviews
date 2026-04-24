<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\Scope;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Scope|Issue $commentable): bool
    {
        return $user->can('view', $commentable);
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
