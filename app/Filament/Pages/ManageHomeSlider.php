<?php

namespace App\Filament\Pages;

use App\Models\HomeSlide;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class ManageHomeSlider extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Slider trang chủ';

    protected static ?string $title = 'Slider trang chủ';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'home-slider';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $uploadData = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('uploadData');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('images')
                    ->label('Ảnh slider')
                    ->helperText('Kéo thả hoặc chọn ảnh. Mỗi ảnh thêm một slide. Khuyến nghị 1920×600 · JPG, PNG, WebP.')
                    ->multiple()
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(10240)
                    ->live()
                    ->afterStateUpdated(function (?array $state, callable $set): void {
                        if (blank($state)) {
                            return;
                        }

                        $added = $this->addSlidesFromUploads($state);

                        $set('images', []);

                        if ($added > 0) {
                            Notification::make()
                                ->title($added === 1 ? 'Đã thêm 1 ảnh' : "Đã thêm {$added} ảnh")
                                ->success()
                                ->send();
                        }
                    }),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thêm ảnh')
                    ->schema([
                        EmbeddedSchema::make('form'),
                    ]),
                Section::make('Ảnh đang hiển thị')
                    ->description('Kéo biểu tượng ≡ để đổi thứ tự. Tắt toggle để ẩn slide khỏi trang chủ.')
                    ->schema([
                        EmbeddedTable::make(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => HomeSlide::query()->with('media'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('Ảnh')
                    ->collection('image')
                    ->conversion('large')
                    ->height(56),
                TextColumn::make('title')
                    ->label('Tên')
                    ->wrap(),
                ToggleColumn::make('is_active')
                    ->label('Hiển thị'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Chưa có ảnh slider')
            ->emptyStateDescription('Upload ảnh ở phần phía trên.')
            ->paginationPageOptions([10, 25, 50]);
    }

    /**
     * @param  array<int, mixed>  $files
     */
    public function addSlidesFromUploads(array $files): int
    {
        $added = 0;

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $slide = $this->createSlideFromFilename($file->getClientOriginalName());
                $slide->addMedia($file->getRealPath())
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('image');

                $added++;

                continue;
            }

            if ($file instanceof TemporaryUploadedFile) {
                if (! $file->exists()) {
                    continue;
                }

                $slide = $this->createSlideFromFilename($file->getClientOriginalName());
                $slide->addMedia($file->getRealPath())
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('image');

                $added++;

                continue;
            }

            if (! is_string($file) || $file === '') {
                continue;
            }

            $disk = (string) config('media-library.disk_name');
            $slide = $this->createSlideFromFilename(basename($file));
            $slide->addMediaFromDisk($file, $disk)
                ->toMediaCollection('image');

            $added++;
        }

        return $added;
    }

    protected function createSlideFromFilename(string $filename): HomeSlide
    {
        return HomeSlide::query()->create([
            'title' => $this->titleFromFilename($filename),
            'sort_order' => HomeSlide::nextSortOrder(),
            'is_active' => true,
        ]);
    }

    protected function titleFromFilename(string $filename): string
    {
        return Str::of(pathinfo($filename, PATHINFO_FILENAME))
            ->replace(['-', '_'], ' ')
            ->title()
            ->toString();
    }
}
