<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\ActivityEventType;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, InteractsWithMedia, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function publicCommunityPosts(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id')
            ->communityPosts()
            ->moderationApproved()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * @return HasMany<ActivityEvent, $this>
     */
    public function activityEvents(): HasMany
    {
        return $this->hasMany(ActivityEvent::class);
    }

    /**
     * @return HasMany<ActivityEvent, $this>
     */
    public function loginEvents(): HasMany
    {
        return $this->hasMany(ActivityEvent::class)
            ->where('event_type', ActivityEventType::Login->value)
            ->latest('occurred_at');
    }

    public function avatarUrl(?string $conversion = 'thumb'): ?string
    {
        $media = $this->getFirstMedia('avatar');

        if ($media === null) {
            return null;
        }

        return $conversion !== null && $media->hasGeneratedConversion($conversion)
            ? $media->getUrl($conversion)
            : $media->getUrl();
    }

    public function initials(): string
    {
        $name = trim($this->name);
        $parts = preg_split('/\s+/', $name) ?: [];

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        if ($name !== '') {
            return mb_strtoupper(mb_substr($name, 0, 2));
        }

        return 'U';
    }

    public function registerMediaCollections(): void
    {
        $disk = (string) config('media-library.disk_name');
        $imageMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes($imageMimes);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->performOnCollections('avatar')
            ->nonQueued();
    }
}
