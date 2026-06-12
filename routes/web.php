<?php

use App\Http\Controllers\CommentReactionController;
use App\Http\Controllers\CommunityCommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\CommunityReactionController;
use App\Http\Controllers\CommunitySavedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberProfileController;
use App\Http\Controllers\NotificationController;
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

Route::get('/members/{user}', [MemberProfileController::class, 'show'])->name('members.show');

Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
Route::post('/community/{article:slug}/comments', [CommunityCommentController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('community.comments.store');
Route::post('/community/{article:slug}/reactions/toggle', [CommunityReactionController::class, 'toggle'])
    ->middleware('throttle:30,1')
    ->name('community.reactions.toggle');

Route::middleware('auth')->group(function () {
    Route::get('/community/me', [CommunityPostController::class, 'myPosts'])->name('community.my-posts');
    Route::get('/community/saved', [CommunitySavedController::class, 'index'])->name('community.saved');
    Route::get('/community/create', [CommunityPostController::class, 'create'])->name('community.create');
    Route::post('/community', [CommunityPostController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('community.store');
    Route::get('/community/{article:slug}/edit', [CommunityPostController::class, 'edit'])->name('community.edit');
    Route::patch('/community/{article:slug}', [CommunityPostController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('community.update');
    Route::post('/workshops/{article:slug}/comments/{comment}/reactions/toggle', [CommentReactionController::class, 'toggleWorkshop'])
        ->middleware('throttle:30,1')
        ->name('workshops.comment-reactions.toggle');
    Route::post('/community/{article:slug}/comments/{comment}/reactions/toggle', [CommentReactionController::class, 'toggleCommunity'])
        ->middleware('throttle:30,1')
        ->name('community.comment-reactions.toggle');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    });
});

Route::get('/community/{article:slug}', [CommunityController::class, 'show'])->name('community.show');

require __DIR__.'/auth.php';
