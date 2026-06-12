<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Models\Category;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Loại')
                    ->options(collect(ArticleType::cases())->mapWithKeys(fn (ArticleType $case): array => [$case->value => $case->label()]))
                    ->default(ArticleType::Announcement->value)
                    ->required()
                    ->live()
                    ->native(false),
                Select::make('category_id')
                    ->label('Danh mục')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->options(static function (): array {
                        $root = Category::systemRoot();
                        if ($root === null) {
                            return [];
                        }

                        return Category::query()
                            ->withDepth()
                            ->defaultOrder()
                            ->whereDescendantOf($root, 'and', false, false)
                            ->get()
                            ->mapWithKeys(static function (Category $category) use ($root): array {
                                $depth = (int) ($category->depth ?? 0);
                                $rootDepth = (int) ($root->depth ?? 0);
                                $indentLevel = max(0, $depth - $rootDepth - 1);
                                $label = str_repeat('— ', $indentLevel).$category->name;

                                return [$category->id => $label];
                            })->all();
                    }),
                TextInput::make('title')
                    ->label('Tiêu đề')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true),
                Textarea::make('excerpt')
                    ->label('Tóm tắt')
                    ->maxLength(500)
                    ->rows(3)
                    ->columnSpanFull(),
                RichEditor::make('body')
                    ->label('Nội dung')
                    ->required()
                    ->extraInputAttributes([
                        'style' => 'min-height: 32rem;',
                    ])
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Trạng thái')
                    ->options(collect(GeneralStatus::cases())->mapWithKeys(fn (GeneralStatus $case): array => [$case->value => $case->label()]))
                    ->default(GeneralStatus::ACTIVE->value)
                    ->required()
                    ->native(false),
                Select::make('moderation_status')
                    ->label('Duyệt UGC')
                    ->options(collect(ArticleModerationStatus::cases())->mapWithKeys(fn (ArticleModerationStatus $case): array => [$case->value => $case->label()]))
                    ->default(ArticleModerationStatus::Approved->value)
                    ->required()
                    ->native(false)
                    ->visible(fn (Get $get): bool => (int) $get('type') === ArticleType::Article->value),
                Textarea::make('moderation_note')
                    ->label('Ghi chú từ chối')
                    ->rows(2)
                    ->maxLength(1000)
                    ->visible(fn (Get $get): bool => (int) $get('type') === ArticleType::Article->value),
                Toggle::make('is_featured')
                    ->label('Nổi bật trang chủ')
                    ->helperText('Chỉ bài viết cộng đồng đã duyệt và đang hiển thị mới xuất hiện trên trang chủ (tối đa 3 bài).')
                    ->visible(fn (Get $get): bool => (int) $get('type') === ArticleType::Article->value),
                DateTimePicker::make('published_at')
                    ->label('Xuất bản lúc')
                    ->seconds(false)
                    ->native(false),
                DateTimePicker::make('starts_at')
                    ->label('Giờ bắt đầu')
                    ->seconds(false)
                    ->native(false)
                    ->visible(fn (Get $get): bool => (int) $get('type') === ArticleType::Announcement->value),
                DateTimePicker::make('ends_at')
                    ->label('Giờ kết thúc')
                    ->seconds(false)
                    ->native(false)
                    ->after('starts_at')
                    ->visible(fn (Get $get): bool => (int) $get('type') === ArticleType::Announcement->value),
                TextInput::make('meta_title')
                    ->label('Meta title')
                    ->maxLength(255),
                Textarea::make('meta_description')
                    ->label('Meta description')
                    ->maxLength(500)
                    ->rows(2)
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('thumbnail')
                    ->label('Ảnh đại diện')
                    ->collection('thumbnail')
                    ->image()
                    ->maxFiles(1)
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label('Thư viện ảnh')
                    ->collection('gallery')
                    ->multiple()
                    ->image()
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }
}
