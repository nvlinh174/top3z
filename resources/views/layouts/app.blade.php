<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ config('pwa.theme_color') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('pwa.short_name') }}">
    @unless (config('seo.allow_indexing'))
        <meta name="robots" content="noindex, nofollow, noarchive">
        <meta name="googlebot" content="noindex, nofollow, noarchive">
    @endunless
    <meta name="description" content="@yield('meta_description', 'Top3z — Makerspace xây dựng và sáng tạo sản phẩm. Workshop thực hành, cộng đồng chia sẻ trải nghiệm.')">

    <title>@yield('title', 'Top3z — Makerspace')</title>

    <link rel="manifest" href="{{ route('manifest') }}">
    <link rel="icon" href="{{ asset('icon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('icon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-shell min-h-screen flex flex-col">
    <x-site.header />

    <main class="site-main flex-1">
        <x-ui.flash-messages />
        @yield('content')
    </main>

    <x-site.footer />
</body>
</html>
