<?php

namespace App\Models;

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Filament\Forms\Components\RichEditor\RichContentAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'moderation_status',
        'moderation_note',
        'submitted_at',
        'views_count',
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
            'moderation_status' => ArticleModerationStatus::class,
            'submitted_at' => 'datetime',
            'views_count' => 'integer',
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
    public function scopeModerationApproved(Builder $query): Builder
    {
        return $query->where('moderation_status', ArticleModerationStatus::Approved);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeModerationPending(Builder $query): Builder
    {
        return $query->where('moderation_status', ArticleModerationStatus::Pending);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeModerationRejected(Builder $query): Builder
    {
        return $query->where('moderation_status', ArticleModerationStatus::Rejected);
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

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeLatestCommunityPosts(Builder $query): Builder
    {
        return $query
            ->communityPosts()
            ->moderationApproved()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public function isPublicCommunityPost(): bool
    {
        return $this->type === ArticleType::Article
            && $this->status === GeneralStatus::ACTIVE
            && $this->moderation_status === ArticleModerationStatus::Approved
            && ($this->published_at === null || $this->published_at <= now());
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

    public function getCoverImageUrl(string $conversion = 'large'): ?string
    {
        $thumbnailUrl = $this->getThumbnailUrl($conversion);

        if ($thumbnailUrl !== null) {
            return $thumbnailUrl;
        }

        $firstGallery = $this->getFirstMedia('gallery');

        return $firstGallery !== null ? $firstGallery->getUrl($conversion) : null;
    }

    public function authorDisplayName(): string
    {
        return $this->author?->name ?? 'Top3z';
    }

    public function authorInitials(): string
    {
        $name = $this->authorDisplayName();
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
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

    /**
     * @return HasMany<ArticleInterest, $this>
     */
    public function interests(): HasMany
    {
        return $this->hasMany(ArticleInterest::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function visibleComments(): HasMany
    {
        return $this->comments()->visible()->latest();
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function rootComments(): HasMany
    {
        return $this->comments()->visible()->roots()->latest();
    }

    public function visibleCommentCount(): int
    {
        return $this->comments()->visible()->count();
    }

    public function hasGuestInterest(string $sessionToken): bool
    {
        return $this->interests()
            ->where('session_token', $sessionToken)
            ->exists();
    }

    public function hasViewerInterest(?int $userId, string $sessionToken): bool
    {
        if ($userId !== null) {
            return $this->interests()
                ->where('user_id', $userId)
                ->exists();
        }

        return $this->hasGuestInterest($sessionToken);
    }
}
