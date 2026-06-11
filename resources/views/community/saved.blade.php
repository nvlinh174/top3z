@extends('layouts.app')

@section('title', 'Bài đã lưu — Top3z')
@section('meta_description', 'Xem lại các bài cộng đồng bạn đã thích hoặc yêu thích tại Top3z.')

@section('content')
    @php
        $tabs = [
            'liked' => ['label' => 'Đã thích', 'count' => $likedCount],
            'favorited' => ['label' => 'Yêu thích', 'count' => $favoritedCount],
        ];
    @endphp

    <section class="border-b border-zinc-800/80 bg-surface-raised/30 py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <x-site.section-heading
                    title="Bài đã lưu"
                    subtitle="Các bài cộng đồng bạn đã thích hoặc đánh dấu yêu thích."
                />

                <x-ui.button variant="secondary" href="{{ route('community.index') }}" class="shrink-0">
                    Khám phá cộng đồng
                </x-ui.button>
            </div>

            <nav class="mt-8 flex flex-wrap gap-2" aria-label="Loại bài đã lưu">
                @foreach ($tabs as $key => $tab)
                    <a
                        href="{{ route('community.saved', ['tab' => $key]) }}"
                        @class([
                            'inline-flex items-center gap-2 rounded-[var(--radius-button)] px-4 py-2 text-sm font-semibold transition',
                            'bg-brand-500 text-zinc-950' => $activeTab === $key,
                            'border border-zinc-700 text-content-muted hover:bg-surface-raised hover:text-content-primary' => $activeTab !== $key,
                        ])
                    >
                        {{ $tab['label'] }}
                        @if ($tab['count'] > 0)
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs',
                                'bg-zinc-950/20' => $activeTab === $key,
                                'bg-surface-overlay' => $activeTab !== $key,
                            ])>{{ $tab['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>
    </section>

    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if ($posts->isEmpty())
                <x-ui.card class="flex flex-col items-center py-12 text-center">
                    <p class="font-display text-lg font-semibold text-content-primary">
                        @if ($activeTab === 'favorited')
                            Chưa có bài yêu thích
                        @else
                            Chưa có bài đã thích
                        @endif
                    </p>
                    <p class="mt-2 max-w-md text-sm text-content-muted">
                        Thích hoặc lưu bài trên trang chi tiết để xem lại tại đây.
                    </p>
                    <x-ui.button href="{{ route('community.index') }}" class="mt-6">
                        Xem bài cộng đồng
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
