<?php

namespace App\Support;

use App\Enums\ActivityEventType;
use App\Enums\ActivitySource;
use App\Models\ActivityEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ActivityTracker
{
    public static function sessionHash(): string
    {
        return hash('sha256', session()->getId().'|'.config('app.key'));
    }

    public static function clientSource(?Request $request = null): ActivitySource
    {
        $request ??= request();
        $header = strtolower(trim((string) $request->header('X-Top3z-Client', '')));

        if ($header === 'android') {
            return ActivitySource::Android;
        }

        return ActivitySource::Web;
    }

    public static function shouldSkipPageViewTracking(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->is_admin;
    }

    /**
     * @return Builder<ActivityEvent>
     */
    public static function queryForPeriod(Carbon $start, Carbon $end): Builder
    {
        return ActivityEvent::query()
            ->where('occurred_at', '>=', $start)
            ->where('occurred_at', '<=', $end);
    }

    public static function countEvents(
        ActivityEventType $type,
        Carbon $start,
        Carbon $end,
        ?ActivitySource $source = null,
    ): int {
        return self::queryForPeriod($start, $end)
            ->where('event_type', $type)
            ->when($source !== null, fn (Builder $query) => $query->where('source', $source))
            ->count();
    }

    public static function uniqueVisitors(Carbon $start, Carbon $end, ?ActivitySource $source = null): int
    {
        return (int) self::queryForPeriod($start, $end)
            ->where('event_type', ActivityEventType::PageView)
            ->when($source !== null, fn (Builder $query) => $query->where('source', $source))
            ->whereNotNull('session_hash')
            ->distinct()
            ->count('session_hash');
    }

    /**
     * @return array{
     *     page_views: int,
     *     unique_visitors: int,
     *     workshop_interests: int,
     *     reactions: int,
     *     comments: int,
     *     registrations: int,
     *     searches: int,
     *     android_page_views: int,
     * }
     */
    public static function summaryForPeriod(Carbon $start, Carbon $end): array
    {
        return [
            'page_views' => self::countEvents(ActivityEventType::PageView, $start, $end),
            'unique_visitors' => self::uniqueVisitors($start, $end),
            'workshop_interests' => self::countEvents(ActivityEventType::WorkshopInterest, $start, $end),
            'reactions' => self::countEvents(ActivityEventType::Reaction, $start, $end),
            'comments' => self::countEvents(ActivityEventType::Comment, $start, $end),
            'registrations' => self::countEvents(ActivityEventType::Register, $start, $end),
            'searches' => self::countEvents(ActivityEventType::Search, $start, $end),
            'android_page_views' => self::countEvents(
                ActivityEventType::PageView,
                $start,
                $end,
                ActivitySource::Android,
            ),
        ];
    }

    /**
     * @return Collection<int, array{date: string, label: string, page_views: int, unique_visitors: int}>
     */
    public static function dailyTrafficSeries(int $days = 30): Collection
    {
        $series = collect();

        for ($daysAgo = $days - 1; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo);
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();

            $series->push([
                'date' => $date->toDateString(),
                'label' => $date->format('d/m'),
                'page_views' => self::countEvents(ActivityEventType::PageView, $start, $end),
                'unique_visitors' => self::uniqueVisitors($start, $end),
            ]);
        }

        return $series;
    }

    /**
     * @return Collection<int, array{
     *     date: string,
     *     label: string,
     *     workshop_interests: int,
     *     reactions: int,
     *     comments: int,
     *     searches: int,
     * }>
     */
    public static function dailyEngagementSeries(int $days = 30): Collection
    {
        $series = collect();

        for ($daysAgo = $days - 1; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo);
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();

            $series->push([
                'date' => $date->toDateString(),
                'label' => $date->format('d/m'),
                'workshop_interests' => self::countEvents(ActivityEventType::WorkshopInterest, $start, $end),
                'reactions' => self::countEvents(ActivityEventType::Reaction, $start, $end),
                'comments' => self::countEvents(ActivityEventType::Comment, $start, $end),
                'searches' => self::countEvents(ActivityEventType::Search, $start, $end),
            ]);
        }

        return $series;
    }

    public static function routeLabel(?string $routeName): string
    {
        if ($routeName === null || $routeName === '') {
            return 'Khác';
        }

        /** @var array<string, string> $labels */
        $labels = config('activity.route_labels', []);

        return $labels[$routeName] ?? $routeName;
    }
}
