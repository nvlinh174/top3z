@props([
    'status',
])

@php
    /** @var \App\Enums\ArticleModerationStatus $status */
    $color = match ($status) {
        \App\Enums\ArticleModerationStatus::Pending => 'border-amber-500/40 bg-amber-500/10 text-amber-300',
        \App\Enums\ArticleModerationStatus::Approved => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
        \App\Enums\ArticleModerationStatus::Rejected => 'border-red-500/40 bg-red-500/10 text-red-300',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {$color}"]) }}>
    {{ $status->label() }}
</span>
