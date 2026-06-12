@props([
    'user',
    'size' => 'md',
])

@php
    /** @var \App\Models\User $user */
    $sizeClasses = match ($size) {
        'sm' => 'size-8 text-xs',
        'lg' => 'size-16 text-lg',
        default => 'size-10 text-sm',
    };
    $avatarUrl = $user->avatarUrl('thumb');
@endphp

@if ($avatarUrl)
    <img
        {{ $attributes->class([$sizeClasses, 'shrink-0 rounded-full object-cover']) }}
        src="{{ $avatarUrl }}"
        alt=""
    >
@else
    <span
        {{ $attributes->class([
            'flex shrink-0 items-center justify-center rounded-full bg-brand-500/15 font-display font-semibold text-brand-400',
            $sizeClasses,
        ]) }}
        aria-hidden="true"
    >
        {{ $user->initials() }}
    </span>
@endif
