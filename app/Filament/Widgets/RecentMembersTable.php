<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentMembersTable extends TableWidget
{
    protected static ?int $sort = 61;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Thành viên mới')
            ->headerActions([
                Action::make('viewAll')
                    ->label('Xem tất cả')
                    ->url(UserResource::getUrl('index')),
            ])
            ->query(fn (): Builder => User::query()
                ->where('is_admin', false)
                ->orderByDesc('created_at')
                ->limit(10))
            ->paginated(false)
            ->emptyStateHeading('Chưa có thành viên')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Đăng ký')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
