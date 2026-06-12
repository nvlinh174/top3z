<?php

namespace App\Http\Controllers;

use App\Actions\RecordCommunityPostView;
use App\Enums\ArticleReactionType;
use App\Enums\ArticleType;
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
            ->with(['category', 'author.media', 'media']);

        if (filled($categorySlug)) {
            $query->whereHas('category', function ($categoryQuery) use ($categorySlug): void {
                $categoryQuery->where('slug', $categorySlug);
            });
        }

        $posts = $query->paginate(12)->withQueryString();

        $categories = Category::query()
            ->whereHas('articles', function ($articleQuery): void {
                $articleQuery->communityPosts()->moderationApproved()->published();
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
        abort_unless($article->type === ArticleType::Article, 404);

        $canView = $article->isPublicCommunityPost()
            || (auth()->check() && auth()->user()->can('view', $article));

        abort_unless($canView, 404);

        if ($article->isPublicCommunityPost()) {
            app(RecordCommunityPostView::class)($article);
            $article->refresh();

            $article->loadCount([
                'reactions as likes_count' => fn ($query) => $query->where('type', ArticleReactionType::Like),
                'reactions as favorites_count' => fn ($query) => $query->where('type', ArticleReactionType::Favorite),
            ]);
        }

        if ($article->isPublicCommunityPost()) {
            $article->load([
                'rootComments' => fn ($query) => $query
                    ->visible()
                    ->withCount('reactions as likes_count')
                    ->with([
                        'user.media',
                        'visibleReplies' => fn ($replyQuery) => $replyQuery
                            ->withCount('reactions as likes_count')
                            ->with(['replyTo', 'user.media']),
                    ]),
            ]);
        }

        $article->load(['category', 'author.media', 'media']);

        $relatedPosts = Article::query()
            ->latestCommunityPosts()
            ->whereKeyNot($article->getKey())
            ->with(['category', 'author.media', 'media'])
            ->limit(3)
            ->get();

        return view('community.show', [
            'post' => $article,
            'relatedPosts' => $relatedPosts,
            'isPreview' => ! $article->isPublicCommunityPost(),
        ]);
    }
}
