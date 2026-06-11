<?php

namespace App\Http\Controllers;

use App\Enums\ArticleType;
use App\Enums\CommentStatus;
use App\Enums\GeneralStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Models\CommentReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentReactionController extends Controller
{
    public function toggleWorkshop(Request $request, Article $article, Comment $comment): JsonResponse
    {
        abort_unless($this->isPublicWorkshop($article), 404);

        return $this->toggle($request, $article, $comment);
    }

    public function toggleCommunity(Request $request, Article $article, Comment $comment): JsonResponse
    {
        abort_unless($article->type === ArticleType::Article, 404);
        abort_unless($article->isPublicCommunityPost(), 404);

        return $this->toggle($request, $article, $comment);
    }

    private function toggle(Request $request, Article $article, Comment $comment): JsonResponse
    {
        abort_unless($comment->article_id === $article->getKey(), 404);
        abort_unless($comment->status === CommentStatus::Active, 404);

        $user = $request->user();

        $existing = CommentReaction::query()
            ->where('comment_id', $comment->getKey())
            ->where('user_id', $user->getKey())
            ->first();

        if ($existing !== null) {
            $existing->delete();
            $active = false;
        } else {
            CommentReaction::query()->create([
                'comment_id' => $comment->getKey(),
                'user_id' => $user->getKey(),
            ]);
            $active = true;
        }

        return response()->json([
            'active' => $active,
            'count' => $comment->reactions()->count(),
        ]);
    }

    private function isPublicWorkshop(Article $article): bool
    {
        return $article->type === ArticleType::Announcement
            && $article->status === GeneralStatus::ACTIVE
            && ($article->published_at === null || $article->published_at <= now());
    }
}
