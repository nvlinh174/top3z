<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class WebManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'name' => config('pwa.name'),
            'short_name' => config('pwa.short_name'),
            'description' => config('pwa.description'),
            'start_url' => '/',
            'scope' => '/',
            'display' => config('pwa.display'),
            'orientation' => 'portrait-primary',
            'theme_color' => config('pwa.theme_color'),
            'background_color' => config('pwa.background_color'),
            'lang' => 'vi',
            'icons' => [
                [
                    'src' => asset('icon.svg'),
                    'sizes' => 'any',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('icon.svg'),
                    'sizes' => 'any',
                    'type' => 'image/svg+xml',
                    'purpose' => 'maskable',
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }
}
