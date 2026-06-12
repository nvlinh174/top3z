<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $upcoming = Article::query()
            ->upcomingWorkshops()
            ->with(['category', 'author', 'media'])
            ->limit(5)
            ->get();

        $featuredWorkshop = $upcoming->first();

        $recentPosts = Article::query()
            ->latestCommunityPosts()
            ->with(['category', 'author.media', 'media'])
            ->limit(3)
            ->get();

        return view('home.index', [
            'featuredWorkshop' => $featuredWorkshop,
            'upcomingWorkshops' => $featuredWorkshop
                ? $upcoming->slice(1)->values()
                : $upcoming,
            'recentPosts' => $recentPosts,
        ]);
    }
}
