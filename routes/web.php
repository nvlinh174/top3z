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

Route::middleware('throttle:60,1')->group(function (): void {
    Route::post('/comment-reactions/{comment}/toggle', [CommentReactionController::class, 'toggle'])
        ->whereNumber('comment')
        ->name('comment-reactions.toggle');
    Route::post('/article-reactions/{article}/toggle', [CommunityReactionController::class, 'toggle'])
        ->whereNumber('article')
        ->name('article-reactions.toggle');
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('workshops')->group(function (): void {
    Route::get('/', [WorkshopController::class, 'index'])->name('workshops.index');
    Route::get('/{article:slug}', [WorkshopController::class, 'show'])->name('workshops.show');

    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('/interests/{article}', [WorkshopInterestController::class, 'store'])
            ->whereNumber('article')
            ->name('workshops.interest.store');
        Route::post('/comments', [WorkshopCommentController::class, 'store'])->name('workshops.comments.store');
    });
});

Route::get('/members/{user}', [MemberProfileController::class, 'show'])->name('members.show');

Route::prefix('community')->group(function (): void {
    Route::get('/', [CommunityController::class, 'index'])->name('community.index');
    Route::post('/comments', [CommunityCommentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('community.comments.store');
});

Route::middleware('auth')->group(function (): void {
    Route::middleware('throttle:60,1')->group(function (): void {
        Route::patch('/community/comments/{comment}', [CommentController::class, 'update'])
            ->name('community.comments.update');
        Route::delete('/community/comments/{comment}', [CommentController::class, 'destroy'])
            ->name('community.comments.destroy');
        Route::patch('/workshops/comments/{comment}', [CommentController::class, 'update'])
            ->name('workshops.comments.update');
        Route::delete('/workshops/comments/{comment}', [CommentController::class, 'destroy'])
            ->name('workshops.comments.destroy');
    });

    Route::prefix('community')->group(function (): void {
        Route::get('/me', [CommunityPostController::class, 'myPosts'])->name('community.my-posts');
        Route::get('/saved', [CommunitySavedController::class, 'index'])->name('community.saved');
        Route::get('/create', [CommunityPostController::class, 'create'])->name('community.create');

        Route::middleware('throttle:30,1')->group(function (): void {
            Route::post('/drafts', [CommunityPostController::class, 'storeDraft'])->name('community.drafts.store');
            Route::patch('/drafts/{article}', [CommunityPostController::class, 'autosaveDraft'])->name('community.drafts.autosave');
            Route::delete('/drafts/{article}', [CommunityPostController::class, 'destroyDraft'])->name('community.drafts.destroy');
        });

        Route::post('/', [CommunityPostController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('community.store');
        Route::get('/{article:slug}/edit', [CommunityPostController::class, 'edit'])->name('community.edit');
        Route::patch('/{article:slug}', [CommunityPostController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('community.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('throttle:60,1')->prefix('notifications')->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    });
});

Route::get('/community/{article:slug}', [CommunityController::class, 'show'])->name('community.show');

require __DIR__.'/auth.php';
