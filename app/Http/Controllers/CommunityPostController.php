<?php

namespace App\Http\Controllers;

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Http\Requests\StoreCommunityPostRequest;
use App\Http\Requests\UpdateCommunityPostRequest;
use App\Models\Article;
use App\Models\Category;
use App\Support\CommunityPostBody;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityPostController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorize('create', Article::class);

        return view('community.create');
    }

    public function store(StoreCommunityPostRequest $request): RedirectResponse
    {
        $this->authorize('create', Article::class);

        $article = Article::query()->create([
            'type' => ArticleType::Article,
            'category_id' => Category::communityPostsCategory()->getKey(),
            'author_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'excerpt' => $request->validated('excerpt'),
            'body' => CommunityPostBody::sanitize($request->validated('body')),
            'status' => GeneralStatus::ACTIVE,
            'moderation_status' => ArticleModerationStatus::Pending,
            'moderation_note' => null,
            'submitted_at' => now(),
            'published_at' => null,
        ]);

        $this->syncMedia($article, $request);

        return redirect()
            ->route('community.my-posts', ['tab' => 'pending'])
            ->with('success', 'Đã gửi bài. Team sẽ duyệt trước khi hiển thị công khai.');
    }

    public function myPosts(Request $request): View
    {
        $tab = $request->query('tab', 'published');

        if (! in_array($tab, ['pending', 'published', 'rejected'], true)) {
            $tab = 'published';
        }

        $status = match ($tab) {
            'pending' => ArticleModerationStatus::Pending,
            'rejected' => ArticleModerationStatus::Rejected,
            default => ArticleModerationStatus::Approved,
        };

        $posts = Article::query()
            ->communityPosts()
            ->where('author_id', $request->user()->id)
            ->where('moderation_status', $status)
            ->with(['category', 'media'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $counts = Article::query()
            ->communityPosts()
            ->where('author_id', $request->user()->id)
            ->selectRaw('moderation_status, count(*) as aggregate')
            ->groupBy('moderation_status')
            ->pluck('aggregate', 'moderation_status');

        return view('community.my-posts', [
            'posts' => $posts,
            'activeTab' => $tab,
            'pendingCount' => (int) ($counts[ArticleModerationStatus::Pending->value] ?? 0),
            'publishedCount' => (int) ($counts[ArticleModerationStatus::Approved->value] ?? 0),
            'rejectedCount' => (int) ($counts[ArticleModerationStatus::Rejected->value] ?? 0),
        ]);
    }

    public function edit(Article $article): View
    {
        $this->authorize('update', $article);
        abort_unless($article->type === ArticleType::Article, 404);

        $article->load('media');

        return view('community.edit', [
            'post' => $article,
            'bodyHtml' => old('body', $article->body ?? ''),
        ]);
    }

    public function update(UpdateCommunityPostRequest $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);
        abort_unless($article->type === ArticleType::Article, 404);

        $wasApproved = $article->moderation_status === ArticleModerationStatus::Approved;

        $article->update([
            'category_id' => Category::communityPostsCategory()->getKey(),
            'title' => $request->validated('title'),
            'excerpt' => $request->validated('excerpt'),
            'body' => CommunityPostBody::sanitize($request->validated('body')),
            'moderation_status' => ArticleModerationStatus::Pending,
            'moderation_note' => null,
            'submitted_at' => now(),
            'published_at' => $wasApproved ? null : $article->published_at,
        ]);

        $this->syncMedia($article, $request);

        $message = $wasApproved
            ? 'Đã cập nhật bài. Bài sẽ ẩn khỏi cộng đồng cho đến khi được duyệt lại.'
            : 'Đã cập nhật bài và gửi lại để duyệt.';

        return redirect()
            ->route('community.my-posts', ['tab' => 'pending'])
            ->with('success', $message);
    }

    private function syncMedia(Article $article, Request $request): void
    {
        if ($request->hasFile('thumbnail')) {
            $article->clearMediaCollection('thumbnail');
            $article
                ->addMediaFromRequest('thumbnail')
                ->toMediaCollection('thumbnail');
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $article
                    ->addMedia($file)
                    ->toMediaCollection('gallery');
            }
        }
    }
}
