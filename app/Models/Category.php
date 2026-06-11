<?php

namespace App\Models;

use App\Enums\GeneralStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasSlug;
    use NodeTrait;

    /**
     * Node gốc duy nhất — luôn là bản ghi id 1 (tạo khi deploy).
     */
    public const SYSTEM_ROOT_ID = 1;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'status',
    ];

    protected $casts = [
        'status' => GeneralStatus::class,
    ];

    public const COMMUNITY_POSTS_SLUG = 'chia-se-trai-nghiem';

    public static function systemRoot(): ?self
    {
        return static::query()->find(self::SYSTEM_ROOT_ID);
    }

    public static function communityPostsCategory(): self
    {
        $root = self::systemRoot();

        if ($root === null) {
            $root = new self([
                'name' => 'System',
                'slug' => 'system',
                'status' => GeneralStatus::ACTIVE,
            ]);
            $root->saveAsRoot();
        }

        return self::query()->firstOrCreate(
            ['slug' => self::COMMUNITY_POSTS_SLUG],
            [
                'name' => 'Chia sẻ trải nghiệm',
                'parent_id' => $root->getKey(),
                'status' => GeneralStatus::ACTIVE,
            ],
        );
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Scope: danh mục hiển thị trong admin (không gồm root id 1).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeVisibleUnderSystemRoot(Builder $query): Builder
    {
        return $query->where('id', '!=', self::SYSTEM_ROOT_ID);
    }

    public function getSlugOptions(): SlugOptions
    {
        $options = SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');

        if ($this->getKey() !== null && (int) $this->getKey() === self::SYSTEM_ROOT_ID) {
            $options->doNotGenerateSlugsOnCreate();
            $options->doNotGenerateSlugsOnUpdate();
        }

        return $options;
    }

    public function storeItem($params = [])
    {
        return $this->create($params);
    }

    /**
     * @return array<int, string>
     */
    public static function optionsForArticleForm(): array
    {
        $root = self::systemRoot();

        if ($root === null) {
            return [];
        }

        return self::query()
            ->withDepth()
            ->defaultOrder()
            ->whereDescendantOf($root, 'and', false, false)
            ->where('status', GeneralStatus::ACTIVE)
            ->get()
            ->mapWithKeys(static function (self $category) use ($root): array {
                $depth = (int) ($category->depth ?? 0);
                $rootDepth = (int) ($root->depth ?? 0);
                $indentLevel = max(0, $depth - $rootDepth - 1);
                $label = str_repeat('— ', $indentLevel).$category->name;

                return [$category->id => $label];
            })
            ->all();
    }
}
