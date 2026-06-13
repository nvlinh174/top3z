<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivityEvent;
use App\Enums\ActivityEventType;
use App\Enums\ArticleReactionType;
use App\Enums\ArticleType;
use App\Http\Requests\ToggleCommunityReactionRequest;
use App\Models\Article;
use App\Models\ArticleReaction;
use App\Support\GuestEngagement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class CommunityReactionController extends Controller
{
    public function toggle(ToggleCommunityReactionRequest $request, Article $article): JsonResponse
    {
        abort_unless($article->type === ArticleType::Article, 404);
        abort_unless($article->isPublicCommunityPost(), 404);

        $type = $request->enum('type', ArticleReactionType::class);
        $user = $request->user();
        $sessionToken = GuestEngagement::sessionToken();

        $existing = ArticleReaction::query()
            ->where('article_id', $article->getKey())
            ->where('type', $type)
            ->when(
                $user !== null,
                fn (Builder $query) => $query->where('user_id', $user->getKey()),
                fn (Builder $query) => $query
                    ->whereNull('user_id')
                    ->where('session_token', $sessionToken),
            )
            ->first();

        if ($existing !== null) {
            $existing->delete();
            $active = false;
        } else {
            ArticleReaction::query()->create([
                'article_id' => $article->getKey(),
                'user_id' => $user?->getKey(),
                'session_token' => $sessionToken,
                'ip_hash' => GuestEngagement::ipHash(),
                'type' => $type,
            ]);

            app(RecordActivityEvent::class)(
                type: ActivityEventType::Reaction,
                subject: $article,
                routeName: 'community.reactions.toggle',
                metadata: ['reaction_type' => $type->value],
            );

            $active = true;
        }

        return response()->json([
            'type' => $type->value,
            'active' => $active,
            'counts' => [
                'like' => $article->reactions()->where('type', ArticleReactionType::Like)->count(),
                'favorite' => $article->reactions()->where('type', ArticleReactionType::Favorite)->count(),
            ],
        ]);
    }
}
