<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\CommentLikedNotification;
use Illuminate\Support\Facades\Cache;

class CommunityCommentLikeNotifications
{
    public static function dispatchForNewLike(Article $article, Comment $comment, ?User $liker): void
    {
        $author = $comment->user;

        if ($author === null) {
            return;
        }

        if ($liker !== null && $author->is($liker)) {
            return;
        }

        $cacheKey = 'comment_liked_notify:'.$comment->getKey();

        if (Cache::has($cacheKey)) {
            return;
        }

        $author->notify(new CommentLikedNotification($article, $comment));

        Cache::put($cacheKey, true, now()->addMinutes(5));
    }
}
