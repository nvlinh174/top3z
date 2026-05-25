<?php

namespace App\Models;

use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Filament\Forms\Components\RichEditor\RichContentAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Article extends Model implements HasMedia, HasRichContent
{
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'published_at',
        'author_id',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'type' => ArticleType::class,
            'status' => GeneralStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $imageMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        $this->addMediaCollection('thumbnail')
            ->singleFile()
            ->acceptsMimeTypes($imageMimes);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes($imageMimes);

        $this->addMediaCollection('content')
            ->acceptsMimeTypes($imageMimes);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('large')
            ->width(1200)
            ->performOnCollections('thumbnail', 'gallery', 'content')
            ->nonQueued();
    }

    public function getRichContentAttribute(string $attribute): ?RichContentAttribute
    {
        if ($attribute !== 'body') {
            return null;
        }

        return RichContentAttribute::make($this, $attribute)
            ->fileAttachmentsDisk(config('media-library.disk_name'))
            ->fileAttachmentsVisibility('public')
            ->fileAttachmentProvider(
                SpatieMediaLibraryFileAttachmentProvider::make()
                    ->collection('content'),
            );
    }

    public function renderRichContent(string $attribute): string
    {
        return $this->getRichContentAttribute($attribute)?->toHtml() ?? (string) $this->getAttribute($attribute);
    }

    public function hasRichContentAttribute(string $attribute): bool
    {
        return $this->getRichContentAttribute($attribute) !== null;
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
