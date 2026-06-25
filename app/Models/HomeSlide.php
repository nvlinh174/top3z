<?php

namespace App\Models;

use Database\Factories\HomeSlideFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HomeSlide extends Model implements HasMedia
{
    /** @use HasFactory<HomeSlideFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function registerMediaCollections(): void
    {
        $disk = (string) config('media-library.disk_name');
        $imageMimes = ['image/jpeg', 'image/png', 'image/webp'];

        $this->addMediaCollection('image')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes($imageMimes);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('large')
            ->width(1920)
            ->performOnCollections('image')
            ->nonQueued();
    }

    public function imageUrl(string $conversion = 'large'): ?string
    {
        $media = $this->getFirstMedia('image');

        if ($media === null) {
            return null;
        }

        return $media->getUrl($conversion);
    }

    public static function nextSortOrder(): int
    {
        return ((int) static::query()->max('sort_order')) + 1;
    }
}
