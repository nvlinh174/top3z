<?php

namespace App\Http\Middleware;

use App\Actions\RecordActivityEvent;
use App\Enums\ActivityEventType;
use App\Support\ActivityTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordPublicPageView
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldRecord($request, $response)) {
            return $response;
        }

        $routeName = $request->route()?->getName();

        app(RecordActivityEvent::class)(
            type: ActivityEventType::PageView,
            routeName: is_string($routeName) ? $routeName : null,
        );

        if ($routeName === 'community.index' && filled($request->query('q'))) {
            app(RecordActivityEvent::class)(
                type: ActivityEventType::Search,
                routeName: $routeName,
                metadata: [
                    'query' => trim((string) $request->query('q')),
                ],
            );
        }

        return $response;
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if (! $response->isSuccessful()) {
            return false;
        }

        if ($request->expectsJson()) {
            return false;
        }

        if (ActivityTracker::shouldSkipPageViewTracking()) {
            return false;
        }

        $routeName = $request->route()?->getName();

        if (! is_string($routeName)) {
            return false;
        }

        return in_array($routeName, config('activity.tracked_page_routes', []), true);
    }
}
