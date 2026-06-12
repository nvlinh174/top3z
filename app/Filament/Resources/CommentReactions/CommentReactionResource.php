<?php

namespace App\Filament\Resources\CommentReactions;

use App\Filament\Resources\CommentReactions\Pages\ManageCommentReactions;
use App\Filament\Resources\CommentReactions\Tables\CommentReactionsTable;
use App\Models\CommentReaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CommentReactionResource extends Resource
{
    protected static ?string $model = CommentReaction::class;

    protected static ?string $modelLabel = 'Reaction bình luận';

    protected static ?string $pluralModelLabel = 'Reaction bình luận';

    protected static ?string $navigationLabel = 'Reaction bình luận';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Tương tác';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return CommentReactionsTable::configure($table);
    }

    /**
     * @return Builder<CommentReaction>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['comment.article', 'comment.user', 'user']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommentReactions::route('/'),
        ];
    }
}
