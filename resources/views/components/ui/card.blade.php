@props([
    'hover' => false,
])

@php
    $classes = 'rounded-[var(--radius-card)] border border-zinc-800/80 bg-surface-raised p-6';

    if ($hover) {
        $classes .= ' transition hover:border-brand-500/30 hover:bg-surface-overlay';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
