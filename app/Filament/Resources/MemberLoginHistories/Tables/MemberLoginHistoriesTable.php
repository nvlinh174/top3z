<?php

namespace App\Filament\Resources\MemberLoginHistories\Tables;

use App\Enums\ActivitySource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberLoginHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Thành viên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('source')
                    ->label('Nguồn')
                    ->badge()
                    ->formatStateUsing(fn (ActivitySource $state): string => $state->label())
                    ->color(fn (ActivitySource $state): string => match ($state) {
                        ActivitySource::Android => 'warning',
                        ActivitySource::Web => 'info',
                    }),
                TextColumn::make('metadata.user_agent')
                    ->label('Thiết bị / trình duyệt')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('occurred_at')
                    ->label('Thời điểm')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('Nguồn')
                    ->options(collect(ActivitySource::cases())->mapWithKeys(
                        fn (ActivitySource $source): array => [$source->value => $source->label()],
                    )->all()),
            ])
            ->recordActions([]);
    }
}
