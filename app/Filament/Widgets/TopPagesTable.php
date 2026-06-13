<?php

namespace App\Filament\Widgets;

use App\Enums\ActivityEventType;
use App\Models\ActivityEvent;
use App\Support\ActivityTracker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopPagesTable extends TableWidget
{
    protected static ?int $sort = 30;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $since = now()->subDays(7)->startOfDay();

        return $table
            ->heading('Top trang 7 ngày')
            ->description('Lượt xem theo khu vực site')
            ->query(fn (): Builder => ActivityEvent::query()
                ->selectRaw('MIN(id) as id, route_name, COUNT(*) as views_count')
                ->where('event_type', ActivityEventType::PageView)
                ->where('occurred_at', '>=', $since)
                ->whereNotNull('route_name')
                ->groupBy('route_name')
                ->orderByDesc('views_count')
                ->limit(10))
            ->paginated(false)
            ->emptyStateHeading('Chưa có lượt xem trang')
            ->emptyStateDescription('Dữ liệu sẽ hiện sau khi có người truy cập site.')
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('route_name')
                    ->label('Trang')
                    ->formatStateUsing(fn (?string $state): string => ActivityTracker::routeLabel($state))
                    ->wrap(),
                TextColumn::make('views_count')
                    ->label('Lượt xem')
                    ->numeric()
                    ->alignEnd(),
            ]);
    }
}
