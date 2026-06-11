@props([
    'workshop',
])

@php
    /** @var \App\Models\Article $workshop */
    $thumbnailUrl = $workshop->getThumbnailUrl();
@endphp

<x-ui.card hover class="flex flex-col sm:flex-row sm:items-stretch gap-0 overflow-hidden p-0">
    <a
        href="{{ route('workshops.show', $workshop) }}"
        class="block shrink-0 sm:w-48 lg:w-56"
        aria-hidden="true"
        tabindex="-1"
    >
        @if ($thumbnailUrl)
            <img
                src="{{ $thumbnailUrl }}"
                alt=""
                class="aspect-[16/10] size-full object-cover sm:aspect-auto sm:h-full"
                loading="lazy"
            >
        @else
            <div class="flex aspect-[16/10] size-full items-center justify-center bg-surface-overlay sm:aspect-auto sm:h-full sm:min-h-32">
                <svg class="size-10 text-brand-500/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-6">
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.badge :variant="$workshop->isUpcomingWorkshop() ? 'upcoming' : 'past'">
                {{ $workshop->isUpcomingWorkshop() ? 'Sắp diễn ra' : 'Đã qua' }}
            </x-ui.badge>
            @if ($workshop->category)
                <span class="text-xs text-content-muted">{{ $workshop->category->name }}</span>
            @endif
        </div>

        <h3 class="mt-3 font-display text-lg font-semibold text-content-primary">
            <a href="{{ route('workshops.show', $workshop) }}" class="hover:text-brand-400">
                {{ $workshop->title }}
            </a>
        </h3>

        @if ($workshop->getFormattedSchedule())
            <p class="mt-2 font-mono text-sm text-brand-400">
                {{ $workshop->getFormattedSchedule() }}
            </p>
        @endif

        @if ($workshop->excerpt)
            <p class="mt-3 line-clamp-2 text-sm text-content-muted">
                {{ $workshop->excerpt }}
            </p>
        @endif
    </div>
</x-ui.card>
