<?php

namespace App\Filament\Resources\ArticleInterests;

use App\Filament\Resources\ArticleInterests\Pages\ManageArticleInterests;
use App\Filament\Resources\ArticleInterests\Tables\ArticleInterestsTable;
use App\Models\ArticleInterest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ArticleInterestResource extends Resource
{
    protected static ?string $model = ArticleInterest::class;

    protected static ?string $modelLabel = 'Quan tâm workshop';

    protected static ?string $pluralModelLabel = 'Quan tâm workshop';

    protected static ?string $navigationLabel = 'Quan tâm sự kiện';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandThumbUp;

    protected static string|UnitEnum|null $navigationGroup = 'Workshop';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ArticleInterestsTable::configure($table);
    }

    /**
     * @return Builder<ArticleInterest>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['article', 'user'])
            ->whereHas('article', fn (Builder $query): Builder => $query->workshops());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageArticleInterests::route('/'),
        ];
    }
}
