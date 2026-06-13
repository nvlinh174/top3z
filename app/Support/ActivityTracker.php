<?php

namespace App\Support;

use App\Enums\ActivityEventType;
use App\Enums\ActivitySource;
use App\Models\ActivityEvent;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

    /**
     * @return array<string, string|null>
     */
    public static function loginMetadata(?Request $request = null): array
    {
        $request ??= request();

        return array_filter([
            'ip_hash' => GuestEngagement::ipHash(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ], fn (?string $value): bool => filled($value));
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
        return (int) self::queryForPeriod($start, $end)
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
        $cacheKey = sprintf('activity.summary.%s.%s', $start->timestamp, $end->timestamp);

        return Cache::remember(
            $cacheKey,
            (int) config('activity.dashboard_cache_seconds', 300),
            fn (): array => self::buildSummaryForPeriod($start, $end),
        );
    }

    /**
     * @return Collection<int, array{date: string, label: string, page_views: int, unique_visitors: int}>
     */
    public static function dailyTrafficSeries(int $days = 30): Collection
    {
        return Cache::remember(
            'activity.traffic_series.'.$days,
            (int) config('activity.dashboard_cache_seconds', 300),
            fn (): Collection => self::buildDailyTrafficSeries($days),
        );
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
        return Cache::remember(
            'activity.engagement_series.'.$days,
            (int) config('activity.dashboard_cache_seconds', 300),
            fn (): Collection => self::buildDailyEngagementSeries($days),
        );
    }

    /**
     * @return Collection<int, array{date: string, label: string, published_posts: int, new_members: int}>
     */
    public static function communityGrowthSeries(int $days = 30): Collection
    {
        return Cache::remember(
            'activity.community_growth_series.'.$days,
            (int) config('activity.dashboard_cache_seconds', 300),
            fn (): Collection => self::buildCommunityGrowthSeries($days),
        );
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

    public static function flushDashboardCache(?Carbon $start = null, ?Carbon $end = null): void
    {
        Cache::forget('activity.traffic_series.30');
        Cache::forget('activity.engagement_series.30');
        Cache::forget('activity.community_growth_series.30');

        if ($start !== null && $end !== null) {
            Cache::forget(sprintf('activity.summary.%s.%s', $start->timestamp, $end->timestamp));
        }
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
    private static function buildSummaryForPeriod(Carbon $start, Carbon $end): array
    {
        $aggregates = ActivityEvent::query()
            ->whereBetween('occurred_at', [$start, $end])
            ->selectRaw(
                'SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as page_views,
                SUM(CASE WHEN event_type = ? AND source = ? THEN 1 ELSE 0 END) as android_page_views,
                SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as workshop_interests,
                SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as reactions,
                SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as comments,
                SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as registrations,
                SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as searches',
                [
                    ActivityEventType::PageView->value,
                    ActivityEventType::PageView->value,
                    ActivitySource::Android->value,
                    ActivityEventType::WorkshopInterest->value,
                    ActivityEventType::Reaction->value,
                    ActivityEventType::Comment->value,
                    ActivityEventType::Register->value,
                    ActivityEventType::Search->value,
                ],
            )
            ->first();

        return [
            'page_views' => (int) ($aggregates->page_views ?? 0),
            'unique_visitors' => self::uniqueVisitors($start, $end),
            'workshop_interests' => (int) ($aggregates->workshop_interests ?? 0),
            'reactions' => (int) ($aggregates->reactions ?? 0),
            'comments' => (int) ($aggregates->comments ?? 0),
            'registrations' => (int) ($aggregates->registrations ?? 0),
            'searches' => (int) ($aggregates->searches ?? 0),
            'android_page_views' => (int) ($aggregates->android_page_views ?? 0),
        ];
    }

    /**
     * @return Collection<int, array{date: string, label: string, page_views: int, unique_visitors: int}>
     */
    private static function buildDailyTrafficSeries(int $days): Collection
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $pageViews = ActivityEvent::query()
            ->where('event_type', ActivityEventType::PageView)
            ->where('occurred_at', '>=', $start)
            ->selectRaw('DATE(occurred_at) as event_date, COUNT(*) as total')
            ->groupBy('event_date')
            ->pluck('total', 'event_date');

        $uniqueVisitors = ActivityEvent::query()
            ->where('event_type', ActivityEventType::PageView)
            ->where('occurred_at', '>=', $start)
            ->whereNotNull('session_hash')
            ->selectRaw('DATE(occurred_at) as event_date, COUNT(DISTINCT session_hash) as total')
            ->groupBy('event_date')
            ->pluck('total', 'event_date');

        return self::buildDailySeries($days, function (string $date) use ($pageViews, $uniqueVisitors): array {
            return [
                'page_views' => (int) ($pageViews[$date] ?? 0),
                'unique_visitors' => (int) ($uniqueVisitors[$date] ?? 0),
            ];
        });
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
    private static function buildDailyEngagementSeries(int $days): Collection
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $counts = ActivityEvent::query()
            ->where('occurred_at', '>=', $start)
            ->whereIn('event_type', [
                ActivityEventType::WorkshopInterest,
                ActivityEventType::Reaction,
                ActivityEventType::Comment,
                ActivityEventType::Search,
            ])
            ->selectRaw('DATE(occurred_at) as event_date, event_type, COUNT(*) as total')
            ->groupBy('event_date', 'event_type')
            ->get()
            ->groupBy('event_date');

        return self::buildDailySeries($days, function (string $date) use ($counts): array {
            /** @var array<string, int> $dayCounts */
            $dayCounts = $counts->get($date, collect())
                ->mapWithKeys(function ($row): array {
                    $type = $row->event_type instanceof ActivityEventType
                        ? $row->event_type->value
                        : (string) $row->event_type;

                    return [$type => (int) $row->total];
                })
                ->all();

            return [
                'workshop_interests' => $dayCounts[ActivityEventType::WorkshopInterest->value] ?? 0,
                'reactions' => $dayCounts[ActivityEventType::Reaction->value] ?? 0,
                'comments' => $dayCounts[ActivityEventType::Comment->value] ?? 0,
                'searches' => $dayCounts[ActivityEventType::Search->value] ?? 0,
            ];
        });
    }

    /**
     * @return Collection<int, array{date: string, label: string, published_posts: int, new_members: int}>
     */
    private static function buildCommunityGrowthSeries(int $days): Collection
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $publishedPosts = Article::query()
            ->communityPosts()
            ->moderationApproved()
            ->where('published_at', '>=', $start)
            ->selectRaw('DATE(published_at) as event_date, COUNT(*) as total')
            ->groupBy('event_date')
            ->pluck('total', 'event_date');

        $newMembers = User::query()
            ->where('is_admin', false)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as event_date, COUNT(*) as total')
            ->groupBy('event_date')
            ->pluck('total', 'event_date');

        return self::buildDailySeries($days, function (string $date) use ($publishedPosts, $newMembers): array {
            return [
                'published_posts' => (int) ($publishedPosts[$date] ?? 0),
                'new_members' => (int) ($newMembers[$date] ?? 0),
            ];
        });
    }

    /**
     * @param  callable(string): array<string, int>  $valuesForDate
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildDailySeries(int $days, callable $valuesForDate): Collection
    {
        $series = collect();

        for ($daysAgo = $days - 1; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo);
            $dateKey = $date->toDateString();

            $series->push(array_merge([
                'date' => $dateKey,
                'label' => $date->format('d/m'),
            ], $valuesForDate($dateKey)));
        }

        return $series;
    }
}
