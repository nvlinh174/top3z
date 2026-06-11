<?php

namespace App\Http\Controllers;

use App\Enums\ArticleType;
use App\Enums\CommentStatus;
use App\Enums\GeneralStatus;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;

class WorkshopCommentController extends Controller
{
    public function store(StoreCommentRequest $request, Article $article): RedirectResponse
    {
        abort_unless($this->isPublicWorkshop($article), 404);

        $parentId = null;
        $replyToId = null;

        if ($request->filled('reply_to_id')) {
            $target = Comment::query()
                ->visible()
                ->where('article_id', $article->getKey())
                ->findOrFail($request->integer('reply_to_id'));

            $placement = Comment::resolveThreadPlacement($target);
            $parentId = $placement['parent_id'];
            $replyToId = $placement['reply_to_id'];
        }

        Comment::query()->create([
            'article_id' => $article->getKey(),
            'parent_id' => $parentId,
            'reply_to_id' => $replyToId,
            'guest_name' => $request->validated('guest_name'),
            'guest_email' => $request->validated('guest_email'),
            'body' => $request->validated('body'),
            'status' => CommentStatus::Active,
        ]);

        return redirect()
            ->route('workshops.show', $article)
            ->withFragment('thao-luan')
            ->with('success', $replyToId ? 'Cảm ơn phản hồi của bạn!' : 'Cảm ơn góp ý của bạn!');
    }

    private function isPublicWorkshop(Article $article): bool
    {
        return $article->type === ArticleType::Announcement
            && $article->status === GeneralStatus::ACTIVE
            && ($article->published_at === null || $article->published_at <= now());
    }
}
