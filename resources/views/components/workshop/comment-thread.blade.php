@props([
    'comment',
    'workshop',
    'storeRoute' => null,
    'reactionContext' => 'workshop',
])

@php
    /** @var \App\Models\Comment $comment */
    /** @var \App\Models\Article $workshop */
@endphp

<div class="text-left">
    <x-workshop.comment-item :comment="$comment" :workshop="$workshop" :store-route="$storeRoute" :reaction-context="$reactionContext" />

    @if ($comment->threadReplies->isNotEmpty())
        <div class="mt-4 space-y-4 border-l border-zinc-800/80 pl-4 sm:mt-5 sm:pl-5 ml-[2.75rem] sm:ml-[3.5rem]">
            @foreach ($comment->threadReplies as $reply)
                <x-workshop.comment-item
                    :comment="$reply"
                    :workshop="$workshop"
                    :store-route="$storeRoute"
                    :reaction-context="$reactionContext"
                    :nested="true"
                />
            @endforeach
        </div>
    @endif
</div>
