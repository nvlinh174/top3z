<?php

namespace App\Actions;

use App\Enums\ActivityEventType;
use App\Models\Article;
use Illuminate\Support\Carbon;

class RecordCommunityPostView
{
    public function __invoke(Article $article): bool
    {
        if (! $article->isPublicCommunityPost()) {
            return false;
        }

        $sessionKey = 'community_view:'.$article->getKey();
        $viewedAt = session($sessionKey);

        if (is_int($viewedAt) && Carbon::createFromTimestamp($viewedAt)->greaterThan(now()->subDay())) {
            return false;
        }

        session([$sessionKey => now()->getTimestamp()]);

        $article->increment('views_count');

        app(RecordActivityEvent::class)(
            type: ActivityEventType::PostView,
            subject: $article,
            routeName: 'community.show',
        );

        return true;
    }
}
