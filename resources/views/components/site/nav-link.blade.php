@props([
    'href' => null,
    'active' => false,
])

@php
    $classes = 'text-sm font-medium transition';

    if ($active) {
        $classes .= ' text-brand-400';
    } elseif ($href) {
        $classes .= ' text-content-muted hover:text-content-primary';
    } else {
        $classes .= ' text-content-muted';
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </span>
@endif
