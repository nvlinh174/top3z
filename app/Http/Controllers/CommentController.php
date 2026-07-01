<?php

namespace App\Http\Controllers;

use App\Enums\ArticleType;
use App\Enums\CommentStatus;
use App\Enums\GeneralStatus;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        $article = $this->resolveCommentArticle($comment);

        $comment->update([
            'body' => $request->validated('body'),
            'edited_at' => now(),
        ]);

        return redirect()
            ->to($this->commentRedirectUrl($article).'#thao-luan')
            ->with('success', 'Đã cập nhật bình luận.');
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        $article = $this->resolveCommentArticle($comment);
        $this->authorize('delete', $comment);

        $comment->update([
            'status' => CommentStatus::Hidden,
        ]);

        return redirect()
            ->to($this->commentRedirectUrl($article).'#thao-luan')
            ->with('success', 'Đã xóa bình luận.');
    }

    private function resolveCommentArticle(Comment $comment): Article
    {
        $article = $comment->article;

        abort_unless($article !== null, 404);

        $this->assertCommentContext($article, $comment);

        return $article;
    }

    private function commentRedirectUrl(Article $article): string
    {
        if ($article->type === ArticleType::Article) {
            return route('community.show', $article);
        }

        return route('workshops.show', $article);
    }

    private function assertCommentContext(Article $article, Comment $comment): void
    {
        abort_unless((int) $comment->article_id === (int) $article->getKey(), 404);

        if ($article->type === ArticleType::Article) {
            abort_unless($article->isPublicCommunityPost(), 404);

            return;
        }

        abort_unless(
            $article->type === ArticleType::Announcement
            && $article->status === GeneralStatus::ACTIVE
            && ($article->published_at === null || $article->published_at <= now()),
            404
        );
    }
}
