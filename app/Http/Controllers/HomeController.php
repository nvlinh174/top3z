<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\HomeSlide;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    private const int HOMEPAGE_COMMUNITY_POST_LIMIT = 6;

    private const int HOMEPAGE_FEATURED_POST_LIMIT = 3;

    public function index(): View
    {
        $upcoming = Article::query()
            ->upcomingWorkshops()
            ->with(['category', 'author', 'media'])
            ->limit(5)
            ->get();

        $featuredWorkshop = $upcoming->first();

        $communityPosts = $this->homepageCommunityPosts();

        return view('home.index', [
            'homeSlides' => HomeSlide::query()
                ->active()
                ->ordered()
                ->with('media')
                ->get(),
            'featuredWorkshop' => $featuredWorkshop,
            'upcomingWorkshops' => $featuredWorkshop
                ? $upcoming->slice(1)->values()
                : $upcoming,
            'communityPosts' => $communityPosts,
        ]);
    }

    /**
     * @return Collection<int, Article>
     */
    private function homepageCommunityPosts(): Collection
    {
        $with = ['category', 'author.media', 'media'];

        $featuredPosts = Article::query()
            ->featuredCommunityPosts()
            ->with($with)
            ->limit(self::HOMEPAGE_FEATURED_POST_LIMIT)
            ->get();

        $remainingSlots = self::HOMEPAGE_COMMUNITY_POST_LIMIT - $featuredPosts->count();

        if ($remainingSlots <= 0) {
            return $featuredPosts;
        }

        $recentPosts = Article::query()
            ->latestCommunityPosts()
            ->where('is_featured', false)
            ->with($with)
            ->limit($remainingSlots)
            ->get();

        return $featuredPosts->concat($recentPosts);
    }
}
