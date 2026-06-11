@extends('layouts.app')

@section('title', ($workshop->meta_title ?: $workshop->title).' — Workshop Top3z')
@section('meta_description', $workshop->meta_description ?: $workshop->excerpt)

@section('content')
    @php
        /** @var \App\Models\Article $workshop */
        $thumbnailUrl = $workshop->getThumbnailUrl();
        $galleryItems = $workshop->getMedia('gallery');
    @endphp

    <section class="border-b border-zinc-800/80 bg-surface-raised/20 py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-content-muted" aria-label="Breadcrumb">
                <a href="{{ route('workshops.index') }}" class="hover:text-brand-400">Lịch workshop</a>
                <span class="mx-2">/</span>
                <span class="text-content-primary">{{ $workshop->title }}</span>
            </nav>
        </div>
    </section>

    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-10">
                <div class="lg:col-span-2">
                    @if ($thumbnailUrl)
                        <img
                            src="{{ $thumbnailUrl }}"
                            alt="{{ $workshop->title }}"
                            class="aspect-[21/9] w-full rounded-[var(--radius-card)] object-cover"
                        >
                    @endif

                    <header class="mt-8">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge :variant="$workshop->isUpcomingWorkshop() ? 'upcoming' : 'past'">
                                {{ $workshop->isUpcomingWorkshop() ? 'Sắp diễn ra' : 'Đã qua' }}
                            </x-ui.badge>
                            @if ($workshop->category)
                                <span class="text-sm text-content-muted">{{ $workshop->category->name }}</span>
                            @endif
                        </div>

                        <h1 class="mt-4 font-display text-3xl font-bold tracking-tight text-content-primary sm:text-4xl">
                            {{ $workshop->title }}
                        </h1>

                        @if ($workshop->excerpt)
                            <p class="mt-4 text-lg text-content-muted">{{ $workshop->excerpt }}</p>
                        @endif
                    </header>

                    @if ($galleryItems->isNotEmpty())
                        <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($galleryItems as $media)
                                <img
                                    src="{{ $media->getUrl('large') }}"
                                    alt=""
                                    class="aspect-square rounded-lg object-cover"
                                    loading="lazy"
                                >
                            @endforeach
                        </div>
                    @endif

                    <div class="prose-workshop mt-10">
                        {!! $workshop->renderRichContent('body') !!}
                    </div>

                    <x-workshop.comments-section :workshop="$workshop" />
                </div>

                <div class="mt-10 lg:col-span-1 lg:mt-0">
                    <div class="lg:sticky lg:top-24">
                        <x-workshop.meta-sidebar :workshop="$workshop" :has-interest="$hasInterest" />
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
