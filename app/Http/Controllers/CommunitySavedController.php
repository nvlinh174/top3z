<?php

namespace App\Http\Controllers;

use App\Enums\ArticleReactionType;
use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CommunitySavedController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'liked');

        if (! in_array($tab, ['liked', 'favorited'], true)) {
            $tab = 'liked';
        }

        $reactionType = $tab === 'favorited'
            ? ArticleReactionType::Favorite
            : ArticleReactionType::Like;

        $posts = $this->savedPostsQuery($request, $reactionType)
            ->paginate(12)
            ->withQueryString();

        return view('community.saved', [
            'posts' => $posts,
            'activeTab' => $tab,
            'likedCount' => $this->countSavedPosts($request, ArticleReactionType::Like),
            'favoritedCount' => $this->countSavedPosts($request, ArticleReactionType::Favorite),
        ]);
    }

    /**
     * @return Builder<Article>
     */
    private function savedPostsQuery(Request $request, ArticleReactionType $type): Builder
    {
        return Article::query()
            ->communityPosts()
            ->moderationApproved()
            ->published()
            ->whereHas('reactions', function (Builder $query) use ($request, $type): void {
                $query
                    ->where('user_id', $request->user()->getKey())
                    ->where('type', $type);
            })
            ->with(['category', 'author', 'media'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    private function countSavedPosts(Request $request, ArticleReactionType $type): int
    {
        return $this->savedPostsQuery($request, $type)->count();
    }
}
