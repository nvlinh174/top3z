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
        abort_unless((int) $comment->article_id === (int) $article->getKey(), 404);
        abort_unless($comment->status === CommentStatus::Active, 404);

        $user = $request->user();
        $sessionToken = GuestEngagement::sessionToken();

        $existing = $this->findViewerReaction($comment, $user, $sessionToken);

        if ($existing !== null) {
            $existing->delete();
            $active = false;
        } else {
            CommentReaction::query()->create([
                'comment_id' => $comment->getKey(),
                'user_id' => $user?->getKey(),
                'session_token' => $user !== null
                    ? $this->memberSessionToken($user)
                    : $sessionToken,
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
        abort_unless((int) $comment->article_id === (int) $article->getKey(), 404);
        abort_unless($comment->status === CommentStatus::Active, 404);

        $user = $request->user();
        abort_unless($user !== null, 401);

        $sessionToken = GuestEngagement::sessionToken();

        $existing = $this->findViewerReaction($comment, $user, $sessionToken);

        if ($existing !== null) {
            $existing->delete();
            $active = false;
        } else {
            CommentReaction::query()->create([
                'comment_id' => $comment->getKey(),
                'user_id' => $user->getKey(),
                'session_token' => $this->memberSessionToken($user),
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

    private function findViewerReaction(Comment $comment, ?User $user, string $sessionToken): ?CommentReaction
    {
        return CommentReaction::query()
            ->where('comment_id', $comment->getKey())
            ->where(function (Builder $query) use ($user, $sessionToken): void {
                if ($user !== null) {
                    $query->where('user_id', $user->getKey())
                        ->orWhere(function (Builder $query) use ($sessionToken): void {
                            $query->whereNull('user_id')->where('session_token', $sessionToken);
                        });
                } else {
                    $query->whereNull('user_id')->where('session_token', $sessionToken);
                }
            })
            ->first();
    }

    private function memberSessionToken(User $user): string
    {
        return hash('sha256', 'member:'.$user->getKey().'|'.config('app.key'));
    }
}
