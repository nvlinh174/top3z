<?php

namespace App\Filament\Widgets;

use App\Enums\ArticleReactionType;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Support\ArticleModerationActions;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopReactedCommunityPostsTable extends TableWidget
{
    protected static ?int $sort = 81;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top reaction all-time')
            ->description('Thích + yêu thích trên bài đã duyệt')
            ->query(fn (): Builder => Article::query()
                ->communityPosts()
                ->moderationApproved()
                ->published()
                ->withCount([
                    'reactions as likes_count' => fn (Builder $query) => $query->where('type', ArticleReactionType::Like),
                    'reactions as favorites_count' => fn (Builder $query) => $query->where('type', ArticleReactionType::Favorite),
                    'reactions as reactions_count',
                ])
                ->orderByDesc('reactions_count')
                ->orderByDesc('published_at')
                ->limit(10))
            ->paginated(false)
            ->emptyStateHeading('Chưa có reaction')
            ->emptyStateDescription('Reaction sẽ hiện khi thành viên thích hoặc lưu bài.')
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->limit(40)
                    ->wrap()
                    ->url(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('reactions_count')
                    ->label('Tổng')
                    ->numeric()
                    ->alignEnd(),
                TextColumn::make('likes_count')
                    ->label('Thích')
                    ->numeric()
                    ->alignEnd(),
                TextColumn::make('favorites_count')
                    ->label('Yêu thích')
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
