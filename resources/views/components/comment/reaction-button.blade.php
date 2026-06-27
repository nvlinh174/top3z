@props([
    'comment',
    'article',
    'reactionContext' => 'workshop',
])

@php
    /** @var \App\Models\Comment $comment */
    /** @var \App\Models\Article $article */

    use App\Support\GuestEngagement;

    $isCommunity = $reactionContext === 'community';
    $sessionToken = GuestEngagement::sessionToken();

    $toggleUrl = route('comment-reactions.toggle', $comment);

    $showUrl = $isCommunity
        ? route('community.show', $article)
        : route('workshops.show', $article);

    $loginUrl = route('login', ['intended' => $showUrl.'#thao-luan']);
    $liked = $comment->hasViewerReaction(auth()->id(), $sessionToken);
@endphp

<div
    {{ $attributes->merge(['class' => 'inline-flex']) }}
    x-data="{
        liked: @js($liked),
        count: @js((int) ($comment->likes_count ?? 0)),
        loading: false,
        async toggle() {
            if (! @js($isCommunity) && ! @js(auth()->check())) {
                window.location.href = @js($loginUrl);

                return;
            }

            if (this.loading) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(@js($toggleUrl), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                    body: JSON.stringify({}),
                });

                if (response.status === 401) {
                    window.location.href = @js($loginUrl);

                    return;
                }

                if (! response.ok) {
                    return;
                }

                const data = await response.json();

                this.liked = data.active;
                this.count = data.count;
            } finally {
                this.loading = false;
            }
        },
    }"
>
    <button
        type="button"
        @click="toggle()"
        :disabled="loading"
        class="inline-flex items-center gap-1 text-xs font-medium text-content-muted transition hover:text-brand-400"
        :aria-pressed="liked"
        aria-label="Yêu thích bình luận"
    >
        <svg
            class="size-4 transition"
            :class="liked ? 'fill-brand-500 text-brand-500' : 'fill-none'"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
        <span x-show="count > 0" x-text="count.toLocaleString('vi-VN')"></span>
    </button>
</div>
