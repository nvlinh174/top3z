@props([
    'workshop',
    'hasInterest' => false,
])

@php
    /** @var \App\Models\Article $workshop */
@endphp

<aside {{ $attributes->merge(['class' => 'rounded-[var(--radius-card)] border border-zinc-800/80 bg-surface-raised p-6']) }}>
    <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-content-muted">
        Thông tin buổi workshop
    </h2>

    <dl class="mt-6 space-y-5 text-sm">
        @if ($workshop->getFormattedSchedule())
            <div>
                <dt class="flex items-center gap-2 text-content-muted">
                    <svg class="size-4 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    Giờ mở cửa
                </dt>
                <dd class="mt-2 font-mono text-content-primary">{{ $workshop->getFormattedSchedule() }}</dd>
            </div>
        @endif

        @if ($workshop->category)
            <div>
                <dt class="text-content-muted">Danh mục</dt>
                <dd class="mt-2 text-content-primary">{{ $workshop->category->name }}</dd>
            </div>
        @endif

        @if ($workshop->author)
            <div>
                <dt class="text-content-muted">Tổ chức</dt>
                <dd class="mt-2 text-content-primary">{{ $workshop->author->name }}</dd>
            </div>
        @endif
    </dl>

    <div class="mt-8 border-t border-zinc-800/80 pt-8">
        @if ($workshop->isUpcomingWorkshop())
            <x-workshop.interest-form :workshop="$workshop" :has-interest="$hasInterest" />
        @else
            <p class="text-center text-sm text-content-muted">
                Buổi workshop đã diễn ra.
            </p>
        @endif
    </div>
</aside>
