<?php

namespace App\Support;

use App\Enums\ArticleModerationStatus;
use App\Models\Article;
use App\Notifications\PostModerationApprovedNotification;
use App\Notifications\PostModerationRejectedNotification;

class CommunityPostModeration
{
    public static function approve(Article $article): void
    {
        $article->update([
            'moderation_status' => ArticleModerationStatus::Approved,
            'published_at' => $article->published_at ?? now(),
            'moderation_note' => null,
        ]);

        $article->refresh();

        if ($article->author !== null) {
            $article->author->notify(new PostModerationApprovedNotification($article));
        }
    }

    public static function reject(Article $article, string $note): void
    {
        $article->update([
            'moderation_status' => ArticleModerationStatus::Rejected,
            'moderation_note' => $note,
        ]);

        $article->refresh();

        if ($article->author !== null) {
            $article->author->notify(new PostModerationRejectedNotification($article));
        }
    }
}
