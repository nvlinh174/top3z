@props([
    'comment',
    'workshop' => null,
    'storeRoute' => null,
    'reactionContext' => 'workshop',
    'nested' => false,
    'showReply' => true,
])

@php
    /** @var \App\Models\Comment $comment */
    /** @var \App\Models\Article|null $workshop */

    use App\Enums\CommentStatus;

    $avatarSize = $nested ? 'sm' : 'md';
    $canManage = auth()->check()
        && $comment->status === CommentStatus::Active
        && auth()->user()->can('update', $comment);

    $updateUrl = null;
    $destroyUrl = null;

    if ($canManage && $workshop) {
        $updateUrl = $reactionContext === 'community'
            ? route('community.comments.update', [$workshop, $comment])
            : route('workshops.comments.update', [$workshop, $comment]);
        $destroyUrl = $reactionContext === 'community'
            ? route('community.comments.destroy', [$workshop, $comment])
            : route('workshops.comments.destroy', [$workshop, $comment]);
    }
@endphp

<article id="comment-{{ $comment->getKey() }}" class="flex items-start gap-3 text-left sm:gap-4">
    @if ($comment->user)
        <x-user.avatar :user="$comment->user" :size="$avatarSize" />
    @else
        <div
            @class([
                'flex shrink-0 items-center justify-center rounded-full bg-brand-500/15 font-display font-semibold text-brand-400',
                'size-8 text-xs' => $nested,
                'size-10 text-sm' => ! $nested,
            ])
            aria-hidden="true"
        >
            {{ $comment->initials() }}
        </div>
    @endif

    <div class="min-w-0 flex-1" @if ($canManage) x-data="{ menuOpen: false, editing: false }" @click.outside="menuOpen = false" @endif>
        <header class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
            @if ($comment->user)
                <a href="{{ route('members.show', $comment->user) }}" class="text-sm font-semibold text-content-primary hover:text-brand-400">
                    {{ $comment->displayName() }}
                </a>
            @else
                <span class="text-sm font-semibold text-content-primary">{{ $comment->displayName() }}</span>
            @endif
            <span class="text-content-muted" aria-hidden="true">·</span>
            <time class="text-xs text-content-muted" datetime="{{ $comment->created_at->toIso8601String() }}">
                {{ $comment->created_at->diffForHumans() }}
            </time>
            @if ($comment->edited_at)
                <span class="text-xs text-content-muted">· đã chỉnh sửa</span>
            @endif

            @if ($canManage)
                <div class="relative ml-auto">
                    <button
                        type="button"
                        class="rounded px-1.5 py-0.5 text-sm text-content-muted hover:bg-surface-overlay hover:text-content-primary"
                        x-on:click="menuOpen = ! menuOpen"
                        aria-label="Tùy chọn bình luận"
                        aria-haspopup="true"
                        x-bind:aria-expanded="menuOpen.toString()"
                    >
                        ⋯
                    </button>

                    <div
                        x-show="menuOpen"
                        x-cloak
                        x-transition
                        class="absolute right-0 z-10 mt-1 w-32 rounded-[var(--radius-button)] border border-zinc-800 bg-surface-raised py-1 shadow-lg"
                    >
                        <button
                            type="button"
                            class="block w-full px-3 py-2 text-left text-sm text-content-primary hover:bg-surface-overlay"
                            x-on:click="editing = true; menuOpen = false"
                        >
                            Sửa
                        </button>
                        <form
                            method="POST"
                            action="{{ $destroyUrl }}"
                            x-on:submit="if (! confirm('Xóa bình luận này?')) { $event.preventDefault(); }"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-left text-sm text-red-400 hover:bg-surface-overlay"
                            >
                                Xóa
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </header>

        @if ($comment->isHidden())
            <p class="mt-1.5 text-sm italic text-content-muted">
                Bình luận đã bị xóa
            </p>
        @elseif ($canManage)
            <div x-show="! editing" class="mt-1.5 text-sm leading-relaxed text-content-primary">
                @if ($comment->replyTo)
                    <span class="font-medium text-brand-400">{{ '@'.$comment->replyTo->mentionName() }}</span>{{ ' ' }}
                @endif
                <span class="whitespace-pre-wrap">{{ $comment->body }}</span>
            </div>

            <form
                x-show="editing"
                x-cloak
                method="POST"
                action="{{ $updateUrl }}"
                class="mt-2 space-y-2"
            >
                @csrf
                @method('PATCH')
                <textarea
                    name="body"
                    rows="3"
                    required
                    maxlength="2000"
                    class="w-full rounded-[var(--radius-button)] border border-zinc-700 bg-surface-base px-3 py-2 text-sm text-content-primary placeholder:text-content-muted focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                >{{ old('body', $comment->body) }}</textarea>
                <div class="flex gap-2">
                    <x-ui.button type="submit" class="!px-3 !py-1.5 !text-xs">
                        Lưu
                    </x-ui.button>
                    <button
                        type="button"
                        class="rounded-[var(--radius-button)] px-3 py-1.5 text-xs font-medium text-content-muted hover:bg-surface-overlay hover:text-content-primary"
                        x-on:click="editing = false"
                    >
                        Hủy
                    </button>
                </div>
            </form>
        @else
            <div class="mt-1.5 text-sm leading-relaxed text-content-primary">
                @if ($comment->replyTo)
                    <span class="font-medium text-brand-400">{{ '@'.$comment->replyTo->mentionName() }}</span>{{ ' ' }}
                @endif
                <span class="whitespace-pre-wrap">{{ $comment->body }}</span>
            </div>
        @endif

        @if ($workshop && ! $comment->isHidden())
            <div class="mt-2.5 flex flex-wrap items-center gap-3">
                <x-comment.reaction-button
                    :comment="$comment"
                    :article="$workshop"
                    :reaction-context="$reactionContext"
                />

                @if ($showReply)
                    <div x-data="{ replying: @json((int) old('reply_to_id') === $comment->getKey()) }">
                        <button
                            type="button"
                            class="cursor-pointer text-xs font-medium text-brand-400 hover:text-brand-300"
                            @click="replying = !replying"
                        >
                            Trả lời
                        </button>

                        <div x-show="replying" x-cloak class="mt-3">
                            <x-workshop.comment-reply-form
                                :workshop="$workshop"
                                :reply-to="$comment"
                                :store-route="$storeRoute"
                            />
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</article>
