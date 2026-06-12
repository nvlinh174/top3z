@extends('layouts.app')

@section('title', 'Cộng đồng — Top3z')
@section('meta_description', 'Chia sẻ trải nghiệm và ảnh sản phẩm sau workshop tại Top3z makerspace.')

@section('content')
    @php
        $hasFilters = filled($searchQuery) || filled($activeCategory) || $activeSort !== \App\Enums\CommunityFeedSort::Latest;
    @endphp

    <section class="border-b border-zinc-800/80 bg-surface-raised/30 py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <x-site.section-heading
                    title="Cộng đồng"
                    subtitle="Trải nghiệm, ảnh sản phẩm và câu chuyện từ các buổi workshop — đọc và lấy cảm hứng."
                />

                @auth
                    <x-ui.button href="{{ route('community.create') }}" class="shrink-0">
                        Viết bài mới
                    </x-ui.button>
                @endauth
            </div>

            <form
                method="GET"
                action="{{ route('community.index') }}"
                class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center"
            >
                @if (filled($activeCategory))
                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                @endif

                @if ($activeSort !== \App\Enums\CommunityFeedSort::Latest)
                    <input type="hidden" name="sort" value="{{ $activeSort->value }}">
                @endif

                <div class="flex-1">
                    <label for="community-search" class="sr-only">Tìm bài viết</label>
                    <input
                        type="search"
                        name="q"
                        id="community-search"
                        value="{{ $searchQuery }}"
                        placeholder="Tìm theo tiêu đề hoặc tóm tắt…"
                        maxlength="100"
                        class="w-full rounded-[var(--radius-button)] border border-zinc-700 bg-surface-base px-3 py-2.5 text-sm text-content-primary placeholder:text-content-muted focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                    >
                </div>

                <x-ui.button type="submit" class="shrink-0 sm:w-auto">
                    Tìm
                </x-ui.button>
            </form>

            <nav class="mt-4 flex flex-wrap gap-2" aria-label="Sắp xếp bài viết">
                @foreach (\App\Enums\CommunityFeedSort::cases() as $sortOption)
                    <a
                        href="{{ route('community.index', $feedRequest->feedQuery(['sort' => $sortOption === \App\Enums\CommunityFeedSort::Latest ? null : $sortOption->value])) }}"
                        @class([
                            'rounded-[var(--radius-button)] px-4 py-2 text-sm font-semibold transition',
                            'bg-brand-500 text-zinc-950' => $activeSort === $sortOption,
                            'border border-zinc-700 text-content-muted hover:bg-surface-raised hover:text-content-primary' => $activeSort !== $sortOption,
                        ])
                    >
                        {{ $sortOption->label() }}
                    </a>
                @endforeach
            </nav>

            @if ($categories->isNotEmpty())
                <nav class="mt-4 flex flex-wrap gap-2" aria-label="Lọc danh mục">
                    <a
                        href="{{ route('community.index', $feedRequest->feedQuery(['category' => null])) }}"
                        @class([
                            'rounded-[var(--radius-button)] px-4 py-2 text-sm font-semibold transition',
                            'bg-brand-500 text-zinc-950' => blank($activeCategory),
                            'border border-zinc-700 text-content-muted hover:bg-surface-raised hover:text-content-primary' => filled($activeCategory),
                        ])
                    >
                        Tất cả
                    </a>
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('community.index', $feedRequest->feedQuery(['category' => $category->slug])) }}"
                            @class([
                                'rounded-[var(--radius-button)] px-4 py-2 text-sm font-semibold transition',
                                'bg-brand-500 text-zinc-950' => $activeCategory === $category->slug,
                                'border border-zinc-700 text-content-muted hover:bg-surface-raised hover:text-content-primary' => $activeCategory !== $category->slug,
                            ])
                        >
                            {{ $category->name }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </section>

    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if ($posts->isEmpty())
                <x-ui.card class="flex flex-col items-center py-12 text-center">
                    @if ($hasFilters)
                        <p class="font-display text-lg font-semibold text-content-primary">
                            Không tìm thấy bài phù hợp
                        </p>
                        <p class="mt-2 max-w-md text-sm text-content-muted">
                            Thử từ khóa khác hoặc bỏ bớt bộ lọc để xem thêm bài viết.
                        </p>
                        <x-ui.button variant="secondary" :href="route('community.index')" class="mt-6">
                            Xem tất cả bài
                        </x-ui.button>
                    @else
                        <p class="font-display text-lg font-semibold text-content-primary">
                            Chưa có bài chia sẻ nào
                        </p>
                        <p class="mt-2 max-w-md text-sm text-content-muted">
                            Quay lại sau — team sẽ đăng trải nghiệm từ các buổi workshop.
                        </p>
                        <x-ui.button variant="secondary" :href="route('home')" class="mt-6">
                            Về trang chủ
                        </x-ui.button>
                    @endif
                </x-ui.card>
            @else
                @if (filled($searchQuery))
                    <p class="mb-6 text-sm text-content-muted">
                        Kết quả cho 「{{ $searchQuery }}」 — {{ $posts->total() }} bài
                    </p>
                @endif

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <x-community.post-card :post="$post" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
