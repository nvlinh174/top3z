<?php

namespace App\Http\Controllers;

use App\Enums\ArticleType;
use App\Enums\CommentStatus;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Article;
use App\Models\Comment;
use App\Support\CommunityCommentNotifications;
use Illuminate\Http\RedirectResponse;

class CommunityCommentController extends Controller
{
    public function store(StoreCommentRequest $request, Article $article): RedirectResponse
    {
        abort_unless($article->type === ArticleType::Article, 404);
        abort_unless($article->isPublicCommunityPost(), 404);

        $parentId = null;
        $replyToId = null;
        $replyTargetId = null;

        if ($request->filled('reply_to_id')) {
            $target = Comment::query()
                ->visible()
                ->where('article_id', $article->getKey())
                ->findOrFail($request->integer('reply_to_id'));

            $replyTargetId = $target->getKey();

            $placement = Comment::resolveThreadPlacement($target);
            $parentId = $placement['parent_id'];
            $replyToId = $placement['reply_to_id'];
        }

        $user = $request->user();

        $comment = Comment::query()->create([
            'article_id' => $article->getKey(),
            'parent_id' => $parentId,
            'reply_to_id' => $replyToId,
            'user_id' => $user?->id,
            'guest_name' => $user ? null : $request->validated('guest_name'),
            'guest_email' => $user ? null : $request->validated('guest_email'),
            'body' => $request->validated('body'),
            'status' => CommentStatus::Active,
        ]);

        CommunityCommentNotifications::dispatchForNewComment(
            article: $article,
            comment: $comment,
            commenter: $user,
            guestName: $user ? null : $request->validated('guest_name'),
            replyTargetId: $replyTargetId,
        );

        return redirect()
            ->route('community.show', $article)
            ->withFragment('thao-luan')
            ->with('success', $replyToId ? 'Cảm ơn phản hồi của bạn!' : 'Cảm ơn bình luận của bạn!');
    }
}
