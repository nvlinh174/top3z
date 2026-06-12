<?php

namespace App\Support;

use App\Enums\ArticleReactionType;
use App\Models\Article;
use App\Models\ArticleReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AuthorCommunityStats
{
    /**
     * @return array{
     *     posts_count: int,
     *     total_views: int,
     *     total_likes: int,
     *     total_favorites: int,
     * }
     */
    public static function summaryFor(User $user): array
    {
        $publishedArticles = self::publishedArticlesQuery($user);

        $aggregates = (clone $publishedArticles)
            ->toBase()
            ->selectRaw('COUNT(*) as posts_count')
            ->selectRaw('COALESCE(SUM(views_count), 0) as total_views')
            ->first();

        return [
            'posts_count' => (int) ($aggregates->posts_count ?? 0),
            'total_views' => (int) ($aggregates->total_views ?? 0),
            'total_likes' => self::reactionCount($publishedArticles, ArticleReactionType::Like),
            'total_favorites' => self::reactionCount($publishedArticles, ArticleReactionType::Favorite),
        ];
    }

    /**
     * @return Collection<int, Article>
     */
    public static function topViewedPostsFor(User $user, int $limit = 3): Collection
    {
        return self::publishedArticlesQuery($user)
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'views_count']);
    }

    /**
     * @return Builder<Article>
     */
    private static function publishedArticlesQuery(User $user): Builder
    {
        return Article::query()
            ->communityPosts()
            ->where('author_id', $user->getKey())
            ->publicCommunityFeed();
    }

    /**
     * @param  Builder<Article>  $publishedArticles
     */
    private static function reactionCount(Builder $publishedArticles, ArticleReactionType $type): int
    {
        return ArticleReaction::query()
            ->where('type', $type)
            ->whereIn('article_id', (clone $publishedArticles)->select('id'))
            ->count();
    }
}
