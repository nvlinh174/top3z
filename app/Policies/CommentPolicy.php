<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        return $comment->isOwnedBy($user);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->isOwnedBy($user);
    }
}
