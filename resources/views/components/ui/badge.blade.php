@props([
    'variant' => 'default',
])

@php
    $variants = [
        'default' => 'border border-zinc-700 bg-surface-raised text-content-muted',
        'upcoming' => 'border border-brand-500/30 bg-brand-500/20 text-brand-400',
        'past' => 'border border-zinc-800 bg-zinc-800/50 text-zinc-400',
    ];

    $classes = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
