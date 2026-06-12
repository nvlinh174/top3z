<?php

namespace App\Filament\Resources\ArticleReactions;

use App\Filament\Resources\ArticleReactions\Pages\ManageArticleReactions;
use App\Filament\Resources\ArticleReactions\Tables\ArticleReactionsTable;
use App\Models\ArticleReaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ArticleReactionResource extends Resource
{
    protected static ?string $model = ArticleReaction::class;

    protected static ?string $modelLabel = 'Reaction bài';

    protected static ?string $pluralModelLabel = 'Reaction bài';

    protected static ?string $navigationLabel = 'Reaction bài';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|UnitEnum|null $navigationGroup = 'Tương tác';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ArticleReactionsTable::configure($table);
    }

    /**
     * @return Builder<ArticleReaction>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['article', 'user'])
            ->whereHas('article', fn (Builder $query): Builder => $query->communityPosts());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageArticleReactions::route('/'),
        ];
    }
}
