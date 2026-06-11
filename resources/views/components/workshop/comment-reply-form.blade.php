@props([
    'workshop',
    'replyTo',
])

@php
    /** @var \App\Models\Article $workshop */
    /** @var \App\Models\Comment $replyTo */
    $hasErrors = (int) old('reply_to_id') === $replyTo->getKey();
@endphp

<div class="mt-4 rounded-lg border border-zinc-800/80 bg-surface-base/50 p-4 sm:p-5">
    <p class="mb-4 text-sm text-content-muted">
        Trả lời <span class="font-medium text-brand-400">{{ '@'.$replyTo->mentionName() }}</span>
    </p>

    <form
        method="POST"
        action="{{ route('workshops.comments.store', $workshop) }}"
        class="flex flex-col gap-4"
        x-data="guestNameForm"
        @submit="remember"
    >
        @csrf
        <input type="hidden" name="reply_to_id" value="{{ $replyTo->getKey() }}">

        <div class="hidden" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <x-workshop.guest-name-field
            :input-id="'guest_name_'.$replyTo->getKey()"
            :server-value="$hasErrors ? old('guest_name', '') : ''"
            :show-errors="$hasErrors"
            context="reply"
        />

        <div>
            <label for="body_{{ $replyTo->getKey() }}" class="mb-2 block text-sm font-medium text-content-primary">
                Phản hồi
            </label>
            <textarea
                name="body"
                id="body_{{ $replyTo->getKey() }}"
                rows="3"
                required
                maxlength="2000"
                placeholder="Viết phản hồi của bạn..."
                class="w-full rounded-[var(--radius-button)] border border-zinc-700 bg-surface-base px-3 py-2.5 text-sm text-content-primary placeholder:text-content-muted focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
            >{{ $hasErrors ? old('body') : '' }}</textarea>
            @if ($hasErrors)
                @error('body')
                    <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <div class="flex flex-wrap gap-3 pt-1">
            <x-ui.button variant="primary" type="submit">
                Gửi trả lời
            </x-ui.button>
            <x-ui.button variant="ghost" type="button" @click="replying = false">
                Huỷ
            </x-ui.button>
        </div>
    </form>
</div>
