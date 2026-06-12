<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = [
        'article_id',
        'user_id',
        'guest_name',
        'guest_email',
        'body',
        'parent_id',
        'reply_to_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Active);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * @return array{parent_id: int, reply_to_id: int|null}
     */
    public static function resolveThreadPlacement(self $target): array
    {
        if ($target->parent_id === null) {
            return [
                'parent_id' => $target->getKey(),
                'reply_to_id' => null,
            ];
        }

        return [
            'parent_id' => $target->parent_id,
            'reply_to_id' => $target->getKey(),
        ];
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function displayName(): string
    {
        if ($this->user !== null) {
            return $this->user->name;
        }

        if (filled($this->guest_name)) {
            return $this->guest_name;
        }

        return 'Khách';
    }

    public function mentionName(): string
    {
        return str_replace(' ', '', $this->displayName());
    }

    public function initials(): string
    {
        $name = $this->displayName();
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function visibleReplies(): HasMany
    {
        return $this->replies()->visible()->oldest();
    }

    /**
     * @return HasMany<CommentReaction, $this>
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function hasUserReaction(User $user): bool
    {
        return $this->reactions()
            ->where('user_id', $user->getKey())
            ->exists();
    }

    public function hasViewerReaction(?int $userId, string $sessionToken): bool
    {
        if ($userId !== null) {
            return $this->reactions()
                ->where('user_id', $userId)
                ->exists();
        }

        return $this->reactions()
            ->whereNull('user_id')
            ->where('session_token', $sessionToken)
            ->exists();
    }
}
