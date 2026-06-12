<?php

namespace App\Policies;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        return $comment->status === CommentStatus::Active
            && $comment->user_id === $user->getKey();
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->status === CommentStatus::Active
            && $comment->user_id === $user->getKey();
    }
}
