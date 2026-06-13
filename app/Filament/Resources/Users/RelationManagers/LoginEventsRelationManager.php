<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\ActivitySource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoginEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'loginEvents';

    protected static ?string $title = 'Lịch sử đăng nhập';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('occurred_at', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Chưa có lần đăng nhập')
            ->emptyStateDescription('Lịch sử sẽ hiện khi thành viên đăng nhập.')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Thời điểm')
                    ->dateTime()
                    ->sortable(),
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
                TextColumn::make('metadata.ip_hash')
                    ->label('IP hash')
                    ->formatStateUsing(fn (?string $state): string => filled($state)
                        ? '…'.substr($state, -8)
                        : '—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
