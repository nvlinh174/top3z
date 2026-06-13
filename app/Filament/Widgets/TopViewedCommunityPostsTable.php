<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Support\ArticleModerationActions;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopViewedCommunityPostsTable extends TableWidget
{
    protected static ?int $sort = 80;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top lượt xem all-time')
            ->description('Tổng views_count trên bài đã duyệt')
            ->query(fn (): Builder => Article::query()
                ->communityPosts()
                ->moderationApproved()
                ->published()
                ->orderByDesc('views_count')
                ->orderByDesc('published_at')
                ->limit(10))
            ->paginated(false)
            ->emptyStateHeading('Chưa có dữ liệu lượt xem')
            ->emptyStateDescription('Lượt xem sẽ hiện khi có người đọc bài cộng đồng.')
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->limit(40)
                    ->wrap()
                    ->url(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('views_count')
                    ->label('Lượt xem')
                    ->numeric()
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Xuất bản')
                    ->dateTime()
                    ->placeholder('—'),
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
