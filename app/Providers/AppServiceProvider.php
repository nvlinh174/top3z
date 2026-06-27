<?php

namespace App\Providers;

use App\Models\Comment;
use App\Policies\CommentPolicy;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Support\Facades\Gate;
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
}
