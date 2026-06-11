<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkshopCommentController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\WorkshopInterestController;
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

Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/workshops/{article:slug}', [WorkshopController::class, 'show'])->name('workshops.show');
Route::post('/workshops/{article:slug}/interest', [WorkshopInterestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('workshops.interest.store');
Route::post('/workshops/{article:slug}/comments', [WorkshopCommentController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('workshops.comments.store');

Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
Route::get('/community/{article:slug}', [CommunityController::class, 'show'])->name('community.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
