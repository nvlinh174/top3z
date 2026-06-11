@extends('layouts.app')

@section('title', 'Cộng đồng — Top3z')
@section('meta_description', 'Chia sẻ trải nghiệm và ảnh sản phẩm sau workshop tại Top3z makerspace.')

@section('content')
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

            @if ($categories->isNotEmpty())
                <nav class="mt-8 flex flex-wrap gap-2" aria-label="Lọc danh mục">
                    <a
                        href="{{ route('community.index') }}"
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
                            href="{{ route('community.index', ['category' => $category->slug]) }}"
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
                    <p class="font-display text-lg font-semibold text-content-primary">
                        Chưa có bài chia sẻ nào
                    </p>
                    <p class="mt-2 max-w-md text-sm text-content-muted">
                        Quay lại sau — team sẽ đăng trải nghiệm từ các buổi workshop.
                    </p>
                    <x-ui.button variant="secondary" :href="route('home')" class="mt-6">
                        Về trang chủ
                    </x-ui.button>
                </x-ui.card>
            @else
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
