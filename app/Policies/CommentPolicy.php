<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, object $commentable): bool
    {
        return $user->can('view', $commentable);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->isEditableBy($user);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->isDeletableBy($user);
    }
}
