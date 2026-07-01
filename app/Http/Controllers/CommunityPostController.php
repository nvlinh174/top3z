<?php

namespace App\Http\Controllers;

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Http\Requests\AutosaveCommunityDraftRequest;
use App\Http\Requests\StoreCommunityPostRequest;
use App\Http\Requests\UpdateCommunityPostRequest;
use App\Models\Article;
use App\Models\Category;
use App\Support\AuthorCommunityStats;
use App\Support\CommunityPostBody;
use App\Support\CommunityPostDraft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityPostController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorize('create', Article::class);

        $draftPost = null;

        if ($request->filled('draft')) {
            $draftPost = Article::query()
                ->communityPosts()
                ->moderationDraft()
                ->where('author_id', $request->user()->id)
                ->where('slug', $request->query('draft'))
                ->first();
        }

        $latestDraft = Article::query()
            ->communityPosts()
            ->moderationDraft()
            ->where('author_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->first();

        if ($draftPost !== null) {
            $latestDraft = null;
        }

        return view('community.create', [
            'draftPost' => $draftPost,
            'latestDraft' => $latestDraft,
        ]);
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

    public function storeDraft(AutosaveCommunityDraftRequest $request): JsonResponse
    {
        if (! $request->hasSavableContent()) {
            return response()->json([
                'message' => 'Chưa có nội dung để lưu nháp.',
            ], 422);
        }

        $article = CommunityPostDraft::createForUser($request->user(), $request->draftPayload());

        return $this->draftJsonResponse($article);
    }

    public function autosaveDraft(AutosaveCommunityDraftRequest $request, Article $article): JsonResponse
    {
        $this->authorize('autosaveDraft', $article);
        abort_unless($article->type === ArticleType::Article, 404);

        if (! $request->hasSavableContent()) {
            return response()->json([
                'message' => 'Chưa có nội dung để lưu nháp.',
            ], 422);
        }

        $article = CommunityPostDraft::updateDraft($article, $request->draftPayload());

        return $this->draftJsonResponse($article);
    }

    public function destroyDraft(Request $request, Article $article): JsonResponse
    {
        $this->authorize('deleteDraft', $article);
        abort_unless($article->type === ArticleType::Article, 404);

        $article->delete();

        return response()->json([
            'deleted' => true,
        ]);
    }

    public function myPosts(Request $request): View
    {
        $tab = $request->query('tab', 'published');

        if (! in_array($tab, ['drafts', 'pending', 'published', 'rejected'], true)) {
            $tab = 'published';
        }

        $postsQuery = Article::query()
            ->communityPosts()
            ->where('author_id', $request->user()->id)
            ->with(['category', 'media']);

        $posts = match ($tab) {
            'drafts' => (clone $postsQuery)
                ->moderationDraft()
                ->orderByDesc('updated_at'),
            'pending' => (clone $postsQuery)
                ->where('moderation_status', ArticleModerationStatus::Pending)
                ->orderByDesc('submitted_at')
                ->orderByDesc('updated_at'),
            'rejected' => (clone $postsQuery)
                ->where('moderation_status', ArticleModerationStatus::Rejected)
                ->orderByDesc('submitted_at')
                ->orderByDesc('updated_at'),
            default => (clone $postsQuery)
                ->where('moderation_status', ArticleModerationStatus::Approved)
                ->orderByDesc('submitted_at')
                ->orderByDesc('updated_at'),
        };

        $posts = $posts->paginate(12)->withQueryString();

        $counts = Article::query()
            ->communityPosts()
            ->where('author_id', $request->user()->id)
            ->selectRaw('moderation_status, count(*) as aggregate')
            ->groupBy('moderation_status')
            ->pluck('aggregate', 'moderation_status');

        return view('community.my-posts', [
            'posts' => $posts,
            'activeTab' => $tab,
            'draftsCount' => (int) ($counts[ArticleModerationStatus::Draft->value] ?? 0),
            'pendingCount' => (int) ($counts[ArticleModerationStatus::Pending->value] ?? 0),
            'publishedCount' => (int) ($counts[ArticleModerationStatus::Approved->value] ?? 0),
            'rejectedCount' => (int) ($counts[ArticleModerationStatus::Rejected->value] ?? 0),
            'authorStats' => AuthorCommunityStats::summaryFor($request->user()),
            'topViewedPosts' => AuthorCommunityStats::topViewedPostsFor($request->user()),
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

        if (CommunityPostDraft::isDraft($article)) {
            $article = CommunityPostDraft::publish($article, $request->validated());
            $this->syncMedia($article, $request);

            return redirect()
                ->route('community.my-posts', ['tab' => 'pending'])
                ->with('success', 'Đã gửi bài. Team sẽ duyệt trước khi hiển thị công khai.');
        }

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

    private function draftJsonResponse(Article $article): JsonResponse
    {
        return response()->json([
            'id' => $article->getKey(),
            'slug' => $article->slug,
            'saved_at' => $article->updated_at?->toIso8601String(),
            'autosave_url' => route('community.drafts.autosave', $article),
            'edit_url' => route('community.edit', $article),
            'update_url' => route('community.update', $article),
            'destroy_url' => route('community.drafts.destroy', $article),
        ]);
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
