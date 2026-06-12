<?php

namespace App\Filament\Resources\ArticleInterests\Tables;

use App\Models\Article;
use App\Models\ArticleInterest;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticleInterestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('article.title')
                    ->label('Workshop')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('participant')
                    ->label('Người quan tâm')
                    ->state(fn (ArticleInterest $record): string => $record->user?->name
                        ?? $record->display_name
                        ?? 'Khách'),
                TextColumn::make('participant_type')
                    ->label('Loại')
                    ->badge()
                    ->state(fn (ArticleInterest $record): string => $record->user_id !== null ? 'Thành viên' : 'Khách')
                    ->color(fn (ArticleInterest $record): string => $record->user_id !== null ? 'info' : 'gray'),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('session_token')
                    ->label('Session')
                    ->state(fn (ArticleInterest $record): string => '…'.substr($record->session_token, -4))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Quan tâm lúc')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('article_id')
                    ->label('Workshop')
                    ->options(fn (): array => Article::query()
                        ->workshops()
                        ->published()
                        ->orderBy('title')
                        ->pluck('title', 'id')
                        ->all())
                    ->searchable(),
                SelectFilter::make('participant_type')
                    ->label('Loại')
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
                Filter::make('created_at')
                    ->label('7 ngày qua')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
