@props([
    'post',
])

@php
    /** @var \App\Models\Article $post */
    $coverUrl = $post->getCoverImageUrl();
@endphp

<div class="group flex h-full flex-col">
    <x-ui.card hover class="flex h-full flex-col overflow-hidden !p-0">
        <a href="{{ route('community.show', $post) }}" class="block flex-1">
            @if ($coverUrl)
                <img
                    src="{{ $coverUrl }}"
                    alt=""
                    class="aspect-[4/3] w-full object-cover transition group-hover:opacity-90"
                    loading="lazy"
                >
            @else
                <div class="aspect-[4/3] w-full bg-gradient-to-br from-brand-500/10 to-zinc-800/50"></div>
            @endif

            <div class="px-5 pt-4 sm:px-6">
                <div class="flex flex-wrap items-center gap-2 text-xs text-content-muted">
                    @if ($post->category)
                        <x-ui.badge>{{ $post->category->name }}</x-ui.badge>
                    @endif
                    @if ($post->published_at)
                        <time datetime="{{ $post->published_at->toIso8601String() }}">
                            {{ $post->published_at->diffForHumans() }}
                        </time>
                    @endif
                </div>

                <h3 class="mt-3 line-clamp-2 font-display text-lg font-semibold text-content-primary group-hover:text-brand-400">
                    {{ $post->title }}
                </h3>

                @if ($post->excerpt)
                    <p class="mt-2 line-clamp-3 text-sm text-content-muted">
                        {{ $post->excerpt }}
                    </p>
                @endif
            </div>
        </a>

        <div class="px-5 pb-5 pt-4 sm:px-6">
            @if ($post->author)
                <a
                    href="{{ route('members.show', $post->author) }}"
                    class="inline-flex items-center gap-2 text-sm text-content-muted transition hover:text-brand-400"
                >
                    <x-user.avatar :user="$post->author" size="sm" />
                    <span>{{ $post->authorDisplayName() }}</span>
                </a>
            @else
                <p class="text-sm text-content-muted">
                    {{ $post->authorDisplayName() }}
                </p>
            @endif
        </div>
    </x-ui.card>
</div>
