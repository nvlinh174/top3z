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
@endphp

<article class="flex items-start gap-3 text-left sm:gap-4">
    <div
        @class([
            'flex shrink-0 items-center justify-center rounded-full bg-brand-500/15 font-display font-semibold text-brand-400',
            'size-9 text-xs' => $nested,
            'size-10 text-sm' => ! $nested,
        ])
        aria-hidden="true"
    >
        {{ $comment->initials() }}
    </div>

    <div class="min-w-0 flex-1">
        <header class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
            <span class="text-sm font-semibold text-content-primary">{{ $comment->displayName() }}</span>
            <span class="text-content-muted" aria-hidden="true">·</span>
            <time class="text-xs text-content-muted" datetime="{{ $comment->created_at->toIso8601String() }}">
                {{ $comment->created_at->diffForHumans() }}
            </time>
        </header>

        <div class="mt-1.5 text-sm leading-relaxed text-content-primary">
            @if ($comment->replyTo)
                <span class="font-medium text-brand-400">{{ '@'.$comment->replyTo->mentionName() }}</span>{{ ' ' }}
            @endif
            <span class="whitespace-pre-wrap">{{ $comment->body }}</span>
        </div>

        @if ($workshop)
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
