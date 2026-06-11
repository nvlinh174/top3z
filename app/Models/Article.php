<?php

namespace App\Models;

use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Filament\Forms\Components\RichEditor\RichContentAttribute;
use Illuminate\Database\Eloquent\Builder;
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
        'starts_at',
        'ends_at',
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
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWorkshops(Builder $query): Builder
    {
        return $query->where('type', ArticleType::Announcement);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCommunityPosts(Builder $query): Builder
    {
        return $query->where('type', ArticleType::Article);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', GeneralStatus::ACTIVE)
            ->where(function (Builder $publishedQuery): void {
                $publishedQuery
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUpcomingWorkshops(Builder $query): Builder
    {
        return $query
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePastWorkshops(Builder $query): Builder
    {
        return $query
            ->whereNotNull('starts_at')
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at');
    }

    public function isUpcomingWorkshop(): bool
    {
        return $this->starts_at !== null && $this->starts_at->isFuture();
    }

    public function getThumbnailUrl(string $conversion = 'large'): ?string
    {
        $url = $this->getFirstMediaUrl('thumbnail', $conversion);

        return $url !== '' ? $url : null;
    }

    public function getFormattedSchedule(): ?string
    {
        if ($this->starts_at === null) {
            return null;
        }

        $formatted = $this->starts_at->format('d/m/Y H:i');

        if ($this->ends_at !== null) {
            $formatted .= ' – '.$this->ends_at->format('H:i');
        }

        return $formatted;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $disk = (string) config('media-library.disk_name');
        $imageMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        $this->addMediaCollection('thumbnail')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes($imageMimes);

        $this->addMediaCollection('gallery')
            ->useDisk($disk)
            ->acceptsMimeTypes($imageMimes);

        $this->addMediaCollection('content')
            ->useDisk($disk)
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
