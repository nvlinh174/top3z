<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                IconColumn::make('is_admin')
                    ->label('Quản trị')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('community_posts_count')
                    ->label('Số bài')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('email_verified_at')
                    ->label('Xác minh email')
                    ->dateTime()
                    ->placeholder('Chưa xác minh')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Đăng ký')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_admin')
                    ->label('Vai trò')
                    ->options([
                        '0' => 'Thành viên',
                        '1' => 'Quản trị',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (User $record): bool => ! $record->is_admin),
            ]);
    }
}
