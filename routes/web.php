<?php

use App\Http\Controllers\CommentController;
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
use App\Http\Controllers\WebManifestController;
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

Route::get('/manifest.webmanifest', WebManifestController::class)->name('manifest');

Route::post('/article-reactions/comments/{comment}/toggle', [CommentReactionController::class, 'toggle'])
    ->middleware('throttle:30,1')
    ->whereNumber('comment')
    ->name('comment-reactions.toggle');
Route::post('/article-reactions/{article}/toggle', [CommunityReactionController::class, 'toggle'])
    ->middleware('throttle:30,1')
    ->whereNumber('article')
    ->name('article-reactions.toggle');
Route::post('/comment-reactions/{comment}/toggle', [CommentReactionController::class, 'toggle'])
    ->middleware('throttle:30,1')
    ->whereNumber('comment');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/workshops/{article:slug}', [WorkshopController::class, 'show'])->name('workshops.show');

// Mutation routes: module prefix + numeric id (e.g. /article-reactions/{id}/toggle).
Route::post('/workshops/interests/{article}', [WorkshopInterestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->whereNumber('article')
    ->name('workshops.interest.store');
Route::post('/workshops/comments', [WorkshopCommentController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('workshops.comments.store');

Route::get('/members/{user}', [MemberProfileController::class, 'show'])->name('members.show');

Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
Route::post('/community/comments', [CommunityCommentController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('community.comments.store');

Route::middleware('auth')->group(function () {
    Route::patch('/community/comments/{comment}', [CommentController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('community.comments.update');
    Route::delete('/community/comments/{comment}', [CommentController::class, 'destroy'])
        ->middleware('throttle:10,1')
        ->name('community.comments.destroy');
    Route::get('/community/me', [CommunityPostController::class, 'myPosts'])->name('community.my-posts');
    Route::get('/community/saved', [CommunitySavedController::class, 'index'])->name('community.saved');
    Route::get('/community/create', [CommunityPostController::class, 'create'])->name('community.create');
    Route::post('/community/drafts', [CommunityPostController::class, 'storeDraft'])
        ->middleware('throttle:30,1')
        ->name('community.drafts.store');
    Route::patch('/community/drafts/{article}', [CommunityPostController::class, 'autosaveDraft'])
        ->middleware('throttle:30,1')
        ->name('community.drafts.autosave');
    Route::delete('/community/drafts/{article}', [CommunityPostController::class, 'destroyDraft'])
        ->middleware('throttle:30,1')
        ->name('community.drafts.destroy');
    Route::post('/community', [CommunityPostController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('community.store');
    Route::get('/community/{article:slug}/edit', [CommunityPostController::class, 'edit'])->name('community.edit');
    Route::patch('/community/{article:slug}', [CommunityPostController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('community.update');
    Route::patch('/workshops/comments/{comment}', [CommentController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('workshops.comments.update');
    Route::delete('/workshops/comments/{comment}', [CommentController::class, 'destroy'])
        ->middleware('throttle:10,1')
        ->name('workshops.comments.destroy');

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
