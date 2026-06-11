@extends('layouts.app')

@section('title', ($post->meta_title ?: $post->title).' — Cộng đồng Top3z')
@section('meta_description', $post->meta_description ?: $post->excerpt)

@section('content')
    @php
        /** @var \App\Models\Article $post */
        $coverUrl = $post->getCoverImageUrl();
    @endphp

    <section class="border-b border-zinc-800/80 bg-surface-raised/20 py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-content-muted" aria-label="Breadcrumb">
                <a href="{{ route('community.index') }}" class="hover:text-brand-400">Cộng đồng</a>
                <span class="mx-2">/</span>
                <span class="text-content-primary">{{ $post->title }}</span>
            </nav>
        </div>
    </section>

    <article class="py-12 sm:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if ($isPreview ?? false)
                <x-community.moderation-banner :post="$post" class="mb-8" />
            @endif

            @if ($coverUrl)
                <img
                    src="{{ $coverUrl }}"
                    alt="{{ $post->title }}"
                    class="aspect-[16/9] w-full rounded-[var(--radius-card)] object-cover"
                >
            @endif

            <header class="mt-8">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($post->category)
                        <x-ui.badge>{{ $post->category->name }}</x-ui.badge>
                    @endif
                    @if ($post->published_at)
                        <time class="text-sm text-content-muted" datetime="{{ $post->published_at->toIso8601String() }}">
                            {{ $post->published_at->format('d/m/Y') }}
                        </time>
                    @endif
                </div>

                <h1 class="mt-4 font-display text-3xl font-bold tracking-tight text-content-primary sm:text-4xl">
                    {{ $post->title }}
                </h1>

                @if ($post->excerpt)
                    <p class="mt-4 text-lg text-content-muted">{{ $post->excerpt }}</p>
                @endif
            </header>

            <x-community.author-box :post="$post" class="mt-8" />

            @can('update', $post)
                <div class="mt-6">
                    <x-ui.button variant="secondary" href="{{ route('community.edit', $post) }}">
                        Sửa bài viết
                    </x-ui.button>
                </div>
            @endcan

            <div class="prose-workshop mt-10">
                {!! $post->renderRichContent('body') !!}
            </div>

            <x-community.gallery :post="$post" class="mt-10" />
        </div>

        @if ($relatedPosts->isNotEmpty())
            <section class="mt-16 border-t border-zinc-800/80 pt-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    <h2 class="font-display text-xl font-semibold text-content-primary">
                        Bài liên quan
                    </h2>

                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($relatedPosts as $related)
                            <x-community.post-card :post="$related" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>
@endsection
