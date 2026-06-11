<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', function () {
    if (config('seo.allow_indexing')) {
        return response("User-agent: *\nAllow: /\n", 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    return response("User-agent: *\nDisallow: /\n", 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('robots');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/workshops', 'pages.placeholder', [
    'title' => 'Lịch workshop',
    'heading' => 'Lịch workshop',
    'message' => 'Trang lịch workshop sẽ có ở Phase 1.',
])->name('workshops.index');

Route::view('/community', 'pages.placeholder', [
    'title' => 'Cộng đồng',
    'heading' => 'Cộng đồng',
    'message' => 'Feed chia sẻ trải nghiệm sẽ có ở Phase 3.',
])->name('community.index');
