@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'max-w-2xl']) }}>
    <h2 class="font-display text-2xl font-bold tracking-tight text-content-primary sm:text-3xl">
        {{ $title }}
    </h2>

    @if ($subtitle)
        <p class="mt-2 text-content-muted">
            {{ $subtitle }}
        </p>
    @endif
</div>
