<?php

namespace App\Models;

use App\Enums\ActivityEventType;
use App\Enums\ActivitySource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityEvent extends Model
{
    protected $fillable = [
        'event_type',
        'subject_type',
        'subject_id',
        'user_id',
        'session_hash',
        'source',
        'route_name',
        'metadata',
        'occurred_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_type' => ActivityEventType::class,
            'source' => ActivitySource::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<ActivityEvent>  $query
     * @return Builder<ActivityEvent>
     */
    public function scopeLogins(Builder $query): Builder
    {
        return $query->where('event_type', ActivityEventType::Login);
    }

    /**
     * @param  Builder<ActivityEvent>  $query
     * @return Builder<ActivityEvent>
     */
    public function scopeMemberLogins(Builder $query): Builder
    {
        return $query
            ->logins()
            ->whereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('is_admin', false));
    }
}
