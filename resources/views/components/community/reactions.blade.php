@props([
    'post',
])

@php
    /** @var \App\Models\Article $post */
    use App\Enums\ArticleReactionType;
    use App\Support\GuestEngagement;

    $sessionToken = GuestEngagement::sessionToken();
    $loginUrl = route('login', ['intended' => route('community.show', $post)]);
@endphp

<div
    {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3']) }}
    x-data="communityReactions({
        toggleUrl: @js(route('article-reactions.toggle', $post)),
        likesCount: @js((int) ($post->likes_count ?? 0)),
        favoritesCount: @js((int) ($post->favorites_count ?? 0)),
        liked: @js($post->hasViewerReaction(auth()->id(), $sessionToken, ArticleReactionType::Like)),
        favorited: @js($post->hasViewerReaction(auth()->id(), $sessionToken, ArticleReactionType::Favorite)),
    })"
>
    <button
        type="button"
        @click="toggle('like')"
        :disabled="loading === 'like'"
        class="inline-flex items-center gap-2 rounded-[var(--radius-button)] border border-zinc-800/80 bg-surface-raised px-4 py-2 text-sm text-content-muted transition hover:border-brand-500/50 hover:text-brand-400"
        :aria-pressed="liked"
        aria-label="Thích bài viết"
    >
        <svg
            class="size-5 transition"
            :class="liked ? 'fill-brand-500 text-brand-500' : 'fill-none'"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.493 19.5h-.001l.004-.001 5.57-.002c.763 0 1.46-.424 1.814-1.101l2.27-4.428a1.125 1.125 0 0 0-.093-1.047L14 9.75h-2.25V6A2.25 2.25 0 0 0 9.5 3.75h-1.5A2.25 2.25 0 0 0 5.75 6v10.5A2.25 2.25 0 0 0 8 18.75h.493ZM15.75 9V6a3.75 3.75 0 0 0-3.75-3.75h-1.5A3.75 3.75 0 0 0 6.75 6v10.5A2.25 2.25 0 0 0 9 18.75h7.493a2.25 2.25 0 0 0 2.122-1.508l2.27-4.428a1.125 1.125 0 0 0-.093-1.047L18 9.75h-2.25Z" />
        </svg>
        <span>Thích</span>
        <span x-show="likesCount > 0" x-text="likesCount.toLocaleString('vi-VN')" class="font-mono text-brand-400"></span>
    </button>

    <button
        type="button"
        @click="toggle('favorite')"
        :disabled="loading === 'favorite'"
        class="inline-flex items-center gap-2 rounded-[var(--radius-button)] border border-zinc-800/80 bg-surface-raised px-4 py-2 text-sm text-content-muted transition hover:border-brand-500/50 hover:text-brand-400"
        :aria-pressed="favorited"
        aria-label="Yêu thích bài viết"
    >
        <svg
            class="size-5 transition"
            :class="favorited ? 'fill-brand-500 text-brand-500' : 'fill-none'"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
        <span>Yêu thích</span>
        <span x-show="favoritesCount > 0" x-text="favoritesCount.toLocaleString('vi-VN')" class="font-mono text-brand-400"></span>
    </button>

    @guest
        <p class="w-full text-xs text-content-muted">
            Không cần tài khoản để thích hoặc lưu bài.
            <a href="{{ $loginUrl }}" class="text-brand-400 hover:underline">Đăng nhập</a> để xem lại trong 「Bài đã lưu」.
        </p>
    @endguest
</div>
