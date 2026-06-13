<?php

namespace App\Actions;

use App\Enums\ActivityEventType;
use App\Enums\ActivitySource;
use App\Models\ActivityEvent;
use App\Support\ActivityTracker;
use Illuminate\Database\Eloquent\Model;

class RecordActivityEvent
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __invoke(
        ActivityEventType $type,
        ?Model $subject = null,
        ?string $routeName = null,
        array $metadata = [],
        ?ActivitySource $source = null,
    ): ActivityEvent {
        return ActivityEvent::query()->create([
            'event_type' => $type,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'user_id' => auth()->id(),
            'session_hash' => ActivityTracker::sessionHash(),
            'source' => $source ?? ActivityTracker::clientSource(),
            'route_name' => $routeName,
            'metadata' => $metadata === [] ? null : $metadata,
            'occurred_at' => now(),
        ]);
    }
}
