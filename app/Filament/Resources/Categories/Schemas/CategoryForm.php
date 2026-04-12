<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tên')
                    ->required()
                    ->maxLength(255),
                Select::make('parent_id')
                    ->label('Danh mục cha')
                    ->helperText('Mặc định là gốc hệ thống; có thể chọn bất kỳ danh mục nào dưới gốc.')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn (): ?int => Category::systemRoot()?->getKey())
                    ->options(static function (Select $component): array {
                        $root = Category::systemRoot();
                        if ($root === null) {
                            return [];
                        }

                        /** @var Category|null $record */
                        $record = $component->getRecord();

                        $query = Category::query()
                            ->withDepth()
                            ->defaultOrder()
                            ->whereDescendantOf($root, 'and', false, true);

                        if ($record instanceof Category && $record->exists) {
                            $excludeIds = $record->descendants()->pluck('id')->push($record->getKey())->all();
                            $query->whereNotIn('id', $excludeIds);
                        }

                        return $query->get()->mapWithKeys(static function (Category $category) use ($root): array {
                            $depth = (int) ($category->depth ?? 0);
                            $rootDepth = (int) ($root->depth ?? 0);
                            $indentLevel = max(0, $depth - $rootDepth);
                            $label = str_repeat('— ', $indentLevel).$category->name;

                            return [$category->id => $label];
                        })->all();
                    }),
            ]);
    }
}
