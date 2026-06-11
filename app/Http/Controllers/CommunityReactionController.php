<?php

namespace App\Http\Controllers;

use App\Enums\ArticleReactionType;
use App\Enums\ArticleType;
use App\Http\Requests\ToggleCommunityReactionRequest;
use App\Models\Article;
use App\Models\ArticleReaction;
use Illuminate\Http\JsonResponse;

class CommunityReactionController extends Controller
{
    public function toggle(ToggleCommunityReactionRequest $request, Article $article): JsonResponse
    {
        abort_unless($article->type === ArticleType::Article, 404);
        abort_unless($article->isPublicCommunityPost(), 404);

        $type = $request->enum('type', ArticleReactionType::class);
        $user = $request->user();

        $existing = ArticleReaction::query()
            ->where('article_id', $article->getKey())
            ->where('user_id', $user->getKey())
            ->where('type', $type)
            ->first();

        if ($existing !== null) {
            $existing->delete();
            $active = false;
        } else {
            ArticleReaction::query()->create([
                'article_id' => $article->getKey(),
                'user_id' => $user->getKey(),
                'type' => $type,
            ]);
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
