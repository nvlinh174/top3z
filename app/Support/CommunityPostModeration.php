<?php

namespace App\Support;

use App\Enums\ArticleModerationStatus;
use App\Models\Article;

class CommunityPostModeration
{
    public static function approve(Article $article): void
    {
        $article->update([
            'moderation_status' => ArticleModerationStatus::Approved,
            'published_at' => $article->published_at ?? now(),
            'moderation_note' => null,
        ]);
    }

    public static function reject(Article $article, string $note): void
    {
        $article->update([
            'moderation_status' => ArticleModerationStatus::Rejected,
            'moderation_note' => $note,
        ]);
    }
}
