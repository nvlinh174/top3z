<?php

namespace App\Filament\Resources\CommentReactions\Tables;

use App\Models\Article;
use App\Models\CommentReaction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentReactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('comment.article.title')
                    ->label('Bài / Workshop')
                    ->searchable()
                    ->sortable()
                    ->limit(35),
                TextColumn::make('comment.body')
                    ->label('Bình luận')
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('comment_author')
                    ->label('Người bình luận')
                    ->state(fn (CommentReaction $record): string => $record->comment?->displayName() ?? '—'),
                TextColumn::make('user.name')
                    ->label('Người thích')
                    ->placeholder('—'),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Thời điểm')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('article_id')
                    ->label('Bài viết')
                    ->options(fn (): array => Article::query()
                        ->orderBy('title')
                        ->pluck('title', 'id')
                        ->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'comment',
                            fn (Builder $commentQuery): Builder => $commentQuery->where('article_id', $data['value'])
                        );
                    })
                    ->searchable(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
