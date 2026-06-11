<?php

namespace App\Http\Controllers;

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
            'published_at' => now(),
        ]);

        $this->syncMedia($article, $request);

        return redirect()
            ->route('community.show', $article)
            ->with('success', 'Bài viết đã được đăng!');
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

        $article->update([
            'category_id' => Category::communityPostsCategory()->getKey(),
            'title' => $request->validated('title'),
            'excerpt' => $request->validated('excerpt'),
            'body' => CommunityPostBody::sanitize($request->validated('body')),
        ]);

        $this->syncMedia($article, $request);

        return redirect()
            ->route('community.show', $article)
            ->with('success', 'Đã cập nhật bài viết.');
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
