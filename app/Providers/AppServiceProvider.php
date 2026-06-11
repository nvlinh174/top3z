<?php

namespace App\Providers;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
