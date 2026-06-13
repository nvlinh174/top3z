<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ArticleModerationActions;
use App\Models\Article;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingCommunityPostsTable extends TableWidget
{
    protected static ?int $sort = 60;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Bài chờ duyệt')
            ->query(fn (): Builder => Article::query()
                ->communityPosts()
                ->moderationPending()
                ->with(['author', 'media'])
                ->orderByDesc('submitted_at')
                ->limit(10))
            ->paginated(false)
            ->emptyStateHeading('Không có bài chờ duyệt')
            ->emptyStateDescription('Các bài member gửi sẽ hiện ở đây.')
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->label('')
                    ->collection('thumbnail')
                    ->conversion('large')
                    ->square()
                    ->size(40),
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('author.name')
                    ->label('Tác giả')
                    ->placeholder('—'),
                TextColumn::make('submitted_at')
                    ->label('Gửi lúc')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->recordActions([
                ArticleModerationActions::approve(),
                ArticleModerationActions::reject(),
                ArticleModerationActions::edit(),
            ]);
    }
}
