<?php

namespace App\Http\Controllers;

use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');

        $query = Article::query()
            ->latestCommunityPosts()
            ->with(['category', 'author', 'media']);

        if (filled($categorySlug)) {
            $query->whereHas('category', function ($categoryQuery) use ($categorySlug): void {
                $categoryQuery->where('slug', $categorySlug);
            });
        }

        $posts = $query->paginate(12)->withQueryString();

        $categories = Category::query()
            ->whereHas('articles', function ($articleQuery): void {
                $articleQuery->communityPosts()->published();
            })
            ->orderBy('name')
            ->get();

        return view('community.index', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $categorySlug,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($this->isPublicCommunityPost($article), 404);

        $article->load(['category', 'author', 'media']);

        $relatedPosts = Article::query()
            ->latestCommunityPosts()
            ->whereKeyNot($article->getKey())
            ->with(['category', 'author', 'media'])
            ->limit(3)
            ->get();

        return view('community.show', [
            'post' => $article,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    private function isPublicCommunityPost(Article $article): bool
    {
        return $article->type === ArticleType::Article
            && $article->status === GeneralStatus::ACTIVE
            && ($article->published_at === null || $article->published_at <= now());
    }
}
