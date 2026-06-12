@props([
    'post',
])

@php
    /** @var \App\Models\Article $post */
    $author = $post->author;
@endphp

@if ($author)
    <a
        href="{{ route('members.show', $author) }}"
        {{ $attributes->class(['flex items-center gap-4 rounded-xl border border-zinc-800/80 bg-surface-raised/50 p-4 transition hover:border-brand-500/30 hover:bg-surface-raised']) }}
    >
        <x-user.avatar :user="$author" size="md" />

        <div>
            <p class="text-sm font-semibold text-content-primary">{{ $post->authorDisplayName() }}</p>
            @if ($post->published_at)
                <p class="text-xs text-content-muted">
                    Đăng {{ $post->published_at->diffForHumans() }}
                </p>
            @endif
        </div>
    </a>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center gap-4 rounded-xl border border-zinc-800/80 bg-surface-raised/50 p-4']) }}>
        <div
            class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand-500/15 font-display text-sm font-semibold text-brand-400"
            aria-hidden="true"
        >
            {{ $post->authorInitials() }}
        </div>

        <div>
            <p class="text-sm font-semibold text-content-primary">{{ $post->authorDisplayName() }}</p>
            @if ($post->published_at)
                <p class="text-xs text-content-muted">
                    Đăng {{ $post->published_at->diffForHumans() }}
                </p>
            @endif
        </div>
    </div>
@endif
