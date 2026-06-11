@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex cursor-pointer items-center justify-center gap-2 rounded-[var(--radius-button)] px-5 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 focus-visible:ring-offset-surface-base disabled:cursor-not-allowed disabled:opacity-60';

    $variants = [
        'primary' => 'bg-brand-500 text-zinc-950 hover:bg-brand-600',
        'secondary' => 'border border-zinc-700 bg-surface-raised text-content-primary hover:border-brand-500/50 hover:bg-surface-overlay',
        'ghost' => 'text-content-muted hover:bg-surface-raised hover:text-content-primary',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
