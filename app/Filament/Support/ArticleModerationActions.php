<?php

namespace App\Filament\Support;

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleType;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Support\CommunityPostModeration;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class ArticleModerationActions
{
    public static function approve(): Action
    {
        return Action::make('approve')
            ->label('Duyệt')
            ->color('success')
            ->visible(fn (Article $record): bool => self::canModerate($record))
            ->action(function (Article $record): void {
                CommunityPostModeration::approve($record);

                Notification::make()
                    ->title('Đã duyệt bài viết')
                    ->success()
                    ->send();
            });
    }

    public static function reject(): Action
    {
        return Action::make('reject')
            ->label('Từ chối')
            ->color('danger')
            ->visible(fn (Article $record): bool => self::canModerate($record))
            ->schema([
                Textarea::make('moderation_note')
                    ->label('Lý do từ chối')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (Article $record, array $data): void {
                CommunityPostModeration::reject($record, $data['moderation_note']);

                Notification::make()
                    ->title('Đã từ chối bài viết')
                    ->warning()
                    ->send();
            });
    }

    public static function edit(): Action
    {
        return Action::make('edit')
            ->label('Sửa')
            ->url(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record]));
    }

    private static function canModerate(Article $record): bool
    {
        return $record->type === ArticleType::Article
            && $record->moderation_status === ArticleModerationStatus::Pending;
    }
}
