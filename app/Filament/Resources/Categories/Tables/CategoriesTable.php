<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Enums\GeneralStatus;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->withDepth()
                ->defaultOrder())
            ->defaultSort('_lft')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên')
                    ->formatStateUsing(function (?string $state, Category $record): string {
                        $root = Category::systemRoot();
                        $depth = (int) ($record->depth ?? 0);
                        $rootDepth = $root ? (int) ($root->depth ?? 0) : 0;
                        $indentLevel = max(0, $depth - $rootDepth - 1);

                        return str_repeat('— ', $indentLevel).((string) ($state ?? ''));
                    })
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (GeneralStatus $state): string => $state->label())
                    ->color(fn (GeneralStatus $state): string => match ($state) {
                        GeneralStatus::ACTIVE => 'success',
                        GeneralStatus::INACTIVE => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
