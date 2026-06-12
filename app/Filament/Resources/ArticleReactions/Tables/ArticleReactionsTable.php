<?php

namespace App\Filament\Resources\ArticleReactions\Tables;

use App\Enums\ArticleReactionType;
use App\Models\Article;
use App\Models\ArticleReaction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticleReactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('article.title')
                    ->label('Bài viết')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('type')
                    ->label('Loại reaction')
                    ->badge()
                    ->formatStateUsing(fn (ArticleReactionType $state): string => $state->label())
                    ->color(fn (ArticleReactionType $state): string => match ($state) {
                        ArticleReactionType::Like => 'info',
                        ArticleReactionType::Favorite => 'warning',
                    }),
                TextColumn::make('participant')
                    ->label('Người reaction')
                    ->state(fn (ArticleReaction $record): string => $record->user?->name ?? 'Khách'),
                TextColumn::make('participant_type')
                    ->label('Loại')
                    ->badge()
                    ->state(fn (ArticleReaction $record): string => $record->user_id !== null ? 'Thành viên' : 'Khách')
                    ->color(fn (ArticleReaction $record): string => $record->user_id !== null ? 'info' : 'gray'),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('session_token')
                    ->label('Session')
                    ->state(fn (ArticleReaction $record): string => '…'.substr($record->session_token, -4))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Thời điểm')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Reaction')
                    ->options(collect(ArticleReactionType::cases())->mapWithKeys(
                        fn (ArticleReactionType $type): array => [$type->value => $type->label()]
                    )->all()),
                SelectFilter::make('participant_type')
                    ->label('Loại người dùng')
                    ->options([
                        'member' => 'Thành viên',
                        'guest' => 'Khách',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'member' => $query->whereNotNull('user_id'),
                            'guest' => $query->whereNull('user_id'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('article_id')
                    ->label('Bài viết')
                    ->options(fn (): array => Article::query()
                        ->communityPosts()
                        ->moderationApproved()
                        ->published()
                        ->orderBy('title')
                        ->pluck('title', 'id')
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
