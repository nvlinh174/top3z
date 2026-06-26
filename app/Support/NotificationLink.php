<?php

namespace App\Support;

class NotificationLink
{
    /**
     * @param  array<string, mixed>  $parameters
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function route(
        string $name,
        array $parameters = [],
        array $query = [],
        ?string $fragment = null,
    ): array {
        $payload = [
            'route' => [
                'name' => $name,
                'parameters' => $parameters,
                'query' => $query,
            ],
        ];

        if ($fragment !== null && $fragment !== '') {
            $payload['fragment'] = ltrim($fragment, '#');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function resolve(array $data): string
    {
        if (isset($data['route']) && is_array($data['route'])) {
            return self::resolveRoute($data['route'], $data['fragment'] ?? null);
        }

        if (isset($data['url']) && is_string($data['url']) && $data['url'] !== '') {
            return self::resolveLegacyUrl($data['url']);
        }

        return route('notifications.index');
    }

    /**
     * @param  array<string, mixed>  $route
     */
    private static function resolveRoute(array $route, ?string $fragment): string
    {
        $name = $route['name'] ?? null;

        if (! is_string($name) || $name === '') {
            return route('notifications.index');
        }

        $parameters = is_array($route['parameters'] ?? null) ? $route['parameters'] : [];
        $query = is_array($route['query'] ?? null) ? $route['query'] : [];

        $url = route($name, [...$parameters, ...$query]);

        if (is_string($fragment) && $fragment !== '') {
            $url .= '#'.ltrim($fragment, '#');
        }

        return $url;
    }

    private static function resolveLegacyUrl(string $storedUrl): string
    {
        $path = parse_url($storedUrl, PHP_URL_PATH) ?: '/';
        $query = parse_url($storedUrl, PHP_URL_QUERY);
        $fragment = parse_url($storedUrl, PHP_URL_FRAGMENT);

        $url = url($path.($query ? '?'.$query : ''));

        if (is_string($fragment) && $fragment !== '') {
            $url .= '#'.$fragment;
        }

        return $url;
    }
}
