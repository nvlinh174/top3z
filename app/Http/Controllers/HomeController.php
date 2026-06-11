<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

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

        return view('home.index', [
            'featuredWorkshop' => $featuredWorkshop,
            'upcomingWorkshops' => $featuredWorkshop
                ? $upcoming->slice(1)->values()
                : $upcoming,
            'recentPosts' => Collection::make(),
        ]);
    }
}
