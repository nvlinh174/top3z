<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home.index', [
            'featuredWorkshop' => null,
            'upcomingWorkshops' => Collection::make(),
            'recentPosts' => Collection::make(),
        ]);
    }
}
