<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Filament\Support\ArticleModerationActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->label('')
                    ->collection('thumbnail')
                    ->conversion('large')
                    ->square()
                    ->size(48),
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Danh mục')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->badge()
                    ->formatStateUsing(fn (ArticleType $state): string => $state->label())
                    ->color('info'),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (GeneralStatus $state): string => $state->label())
                    ->color(fn (GeneralStatus $state): string => match ($state) {
                        GeneralStatus::ACTIVE => 'success',
                        GeneralStatus::INACTIVE => 'gray',
                    }),
                TextColumn::make('moderation_status')
                    ->label('Duyệt UGC')
                    ->badge()
                    ->formatStateUsing(fn (ArticleModerationStatus $state): string => $state->label())
                    ->color(fn (ArticleModerationStatus $state): string => match ($state) {
                        ArticleModerationStatus::Pending => 'warning',
                        ArticleModerationStatus::Approved => 'success',
                        ArticleModerationStatus::Rejected => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('author.name')
                    ->label('Tác giả')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Xuất bản')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('starts_at')
                    ->label('Workshop')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Loại')
                    ->options(collect(ArticleType::cases())->mapWithKeys(fn (ArticleType $case): array => [$case->value => $case->label()])),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(collect(GeneralStatus::cases())->mapWithKeys(fn (GeneralStatus $case): array => [$case->value => $case->label()])),
                SelectFilter::make('category_id')
                    ->label('Danh mục')
                    ->relationship('category', 'name', modifyQueryUsing: fn ($query) => $query->visibleUnderSystemRoot()->defaultOrder()),
                SelectFilter::make('moderation_status')
                    ->label('Duyệt UGC')
                    ->options(collect(ArticleModerationStatus::cases())->mapWithKeys(fn (ArticleModerationStatus $case): array => [$case->value => $case->label()])),
            ])
            ->recordActions([
                ArticleModerationActions::approve(),
                ArticleModerationActions::reject(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
