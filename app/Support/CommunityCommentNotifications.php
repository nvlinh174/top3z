<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\CommentOnYourPostNotification;
use App\Notifications\CommentReplyNotification;

class CommunityCommentNotifications
{
    public static function dispatchForNewComment(
        Article $article,
        Comment $comment,
        ?User $commenter,
        ?string $guestName,
        ?int $replyTargetId,
    ): void {
        $actorName = $commenter?->name ?? $guestName ?? 'Khách';

        if ($replyTargetId !== null) {
            $target = Comment::query()
                ->visible()
                ->where('article_id', $article->getKey())
                ->with('user')
                ->find($replyTargetId);

            if ($target?->user !== null && $target->user->isNot($commenter)) {
                $target->user->notify(new CommentReplyNotification(
                    article: $article,
                    comment: $comment,
                    actorName: $actorName,
                ));
            }

            return;
        }

        $author = $article->author;

        if ($author !== null && $author->isNot($commenter)) {
            $author->notify(new CommentOnYourPostNotification(
                article: $article,
                actorName: $actorName,
            ));
        }
    }
}
