<?php

namespace App\Http\Controllers;

use App\Enums\ArticleType;
use App\Enums\CommentStatus;
use App\Enums\GeneralStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\User;
use App\Support\CommunityCommentLikeNotifications;
use App\Support\GuestEngagement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentReactionController extends Controller
{
    public function toggle(Request $request, Comment $comment): JsonResponse
    {
        $article = $comment->article;

        abort_unless($article !== null, 404);

        if ($article->type === ArticleType::Article) {
            abort_unless($this->canLikeCommunityComment($article), 404);

            return $this->toggleForCommunity($request, $article, $comment);
        }

        abort_unless($this->isPublicWorkshop($article), 404);

        return $this->toggleForWorkshop($request, $article, $comment);
    }

    private function canLikeCommunityComment(Article $article): bool
    {
        if ($article->type !== ArticleType::Article) {
            return false;
        }

        if ($article->isPublicCommunityPost()) {
            return true;
        }

        $user = auth()->user();

        return $user instanceof User && $user->can('view', $article);
    }

    private function toggleForCommunity(Request $request, Article $article, Comment $comment): JsonResponse
    {
        abort_unless($comment->article_id === $article->getKey(), 404);
        abort_unless($comment->status === CommentStatus::Active, 404);

        $user = $request->user();
        $sessionToken = GuestEngagement::sessionToken();

        $existing = CommentReaction::query()
            ->where('comment_id', $comment->getKey())
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
            CommentReaction::query()->create([
                'comment_id' => $comment->getKey(),
                'user_id' => $user?->getKey(),
                'session_token' => $sessionToken,
                'ip_hash' => GuestEngagement::ipHash(),
            ]);
            $active = true;

            CommunityCommentLikeNotifications::dispatchForNewLike($article, $comment, $user);
        }

        return response()->json([
            'active' => $active,
            'count' => $comment->reactions()->count(),
        ]);
    }

    private function toggleForWorkshop(Request $request, Article $article, Comment $comment): JsonResponse
    {
        abort_unless($comment->article_id === $article->getKey(), 404);
        abort_unless($comment->status === CommentStatus::Active, 404);

        $user = $request->user();
        abort_unless($user !== null, 401);

        $sessionToken = GuestEngagement::sessionToken();

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
                'session_token' => $sessionToken,
                'ip_hash' => GuestEngagement::ipHash(),
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
