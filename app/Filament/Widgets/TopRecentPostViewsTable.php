<?php

namespace App\Filament\Widgets;

use App\Enums\ActivityEventType;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Support\ArticleModerationActions;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopRecentPostViewsTable extends TableWidget
{
    protected static ?int $sort = 31;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $since = now()->subDays(7)->startOfDay();

        return $table
            ->heading('Top bài xem nhiều 7 ngày')
            ->description('Theo event log — không phải all-time')
            ->query(fn (): Builder => Article::query()
                ->communityPosts()
                ->moderationApproved()
                ->published()
                ->whereHas('activityEvents', fn (Builder $query) => $query
                    ->where('event_type', ActivityEventType::PostView)
                    ->where('occurred_at', '>=', $since))
                ->withCount([
                    'activityEvents as recent_views_count' => fn (Builder $query) => $query
                        ->where('event_type', ActivityEventType::PostView)
                        ->where('occurred_at', '>=', $since),
                ])
                ->orderByDesc('recent_views_count')
                ->orderByDesc('published_at')
                ->limit(10))
            ->paginated(false)
            ->emptyStateHeading('Chưa có lượt xem bài')
            ->emptyStateDescription('Dữ liệu sẽ hiện khi có người đọc bài cộng đồng.')
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->limit(40)
                    ->wrap()
                    ->url(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('recent_views_count')
                    ->label('7 ngày')
                    ->numeric()
                    ->alignEnd(),
            ])
            ->recordActions([
                ArticleModerationActions::edit(),
                Action::make('view_public')
                    ->label('Xem')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Article $record): string => route('community.show', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}
