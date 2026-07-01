<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Policies\ArticlePolicy;
use App\Policies\CommentPolicy;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        $mediaDisk = (string) config('media-library.disk_name');

        SpatieMediaLibraryFileUpload::configureUsing(
            function (SpatieMediaLibraryFileUpload $component) use ($mediaDisk): void {
                $component
                    ->disk($mediaDisk)
                    ->conversionsDisk($mediaDisk);
            },
        );

        RichEditor::configureUsing(
            function (RichEditor $component) use ($mediaDisk): void {
                $component
                    ->fileAttachmentsDisk($mediaDisk)
                    ->fileAttachmentsVisibility('public');
            },
        );
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('community-draft', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('community-post', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('comment-mutations', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('community-reactions', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('community-comments', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('workshop-engagement', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });
    }
}
