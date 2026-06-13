<?php

namespace App\Filament\Resources\MemberLoginHistories;

use App\Filament\Resources\MemberLoginHistories\Pages\ManageMemberLoginHistories;
use App\Filament\Resources\MemberLoginHistories\Tables\MemberLoginHistoriesTable;
use App\Models\ActivityEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MemberLoginHistoryResource extends Resource
{
    protected static ?string $model = ActivityEvent::class;

    protected static ?string $modelLabel = 'Lịch sử đăng nhập';

    protected static ?string $pluralModelLabel = 'Lịch sử đăng nhập';

    protected static ?string $navigationLabel = 'Lịch sử đăng nhập';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowRightOnRectangle;

    protected static string|UnitEnum|null $navigationGroup = 'Tương tác';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return MemberLoginHistoriesTable::configure($table);
    }

    /**
     * @return Builder<ActivityEvent>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->memberLogins()
            ->with('user');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMemberLoginHistories::route('/'),
        ];
    }
}
