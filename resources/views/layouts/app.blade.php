<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @unless (config('seo.allow_indexing'))
        <meta name="robots" content="noindex, nofollow, noarchive">
        <meta name="googlebot" content="noindex, nofollow, noarchive">
    @endunless
    <meta name="description" content="@yield('meta_description', 'Top3z — Makerspace xây dựng và sáng tạo sản phẩm. Workshop thực hành, cộng đồng chia sẻ trải nghiệm.')">

    <title>@yield('title', 'Top3z — Makerspace')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">
    <x-site.header />

    <main class="flex-1">
        <x-ui.flash-messages />
        @yield('content')
    </main>

    <x-site.footer />
</body>
</html>
