<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Enums\CommentStatus;
use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommentsTable
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
                TextColumn::make('display_name')
                    ->label('Người gửi')
                    ->state(fn (Comment $record): string => $record->displayName()),
                TextColumn::make('reply_to_id')
                    ->label('Trả lời')
                    ->state(fn (Comment $record): string => $record->replyTo ? '@'.$record->replyTo->mentionName() : '—')
                    ->placeholder('—'),
                TextColumn::make('body')
                    ->label('Nội dung')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (CommentStatus $state): string => $state->label())
                    ->color(fn (CommentStatus $state): string => match ($state) {
                        CommentStatus::Active => 'success',
                        CommentStatus::Hidden => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Gửi lúc')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(collect(CommentStatus::cases())->mapWithKeys(fn (CommentStatus $case): array => [$case->value => $case->label()])),
            ])
            ->recordActions([
                Action::make('hide')
                    ->label('Ẩn')
                    ->color('warning')
                    ->visible(fn (Comment $record): bool => $record->status === CommentStatus::Active)
                    ->action(fn (Comment $record): bool => $record->update(['status' => CommentStatus::Hidden])),
                Action::make('show')
                    ->label('Hiện')
                    ->color('success')
                    ->visible(fn (Comment $record): bool => $record->status === CommentStatus::Hidden)
                    ->action(fn (Comment $record): bool => $record->update(['status' => CommentStatus::Active])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
