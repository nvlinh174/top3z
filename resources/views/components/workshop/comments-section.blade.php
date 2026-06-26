@props([
    'workshop',
    'storeRoute' => null,
    'context' => 'workshop',
    'reactionContext' => 'workshop',
])

@php
    /** @var \App\Models\Article $workshop */
    $rootComments = $workshop->rootComments;
    $commentCount = $workshop->visibleCommentCount();
    $isCommunity = $context === 'community';
    $storeUrl = $storeRoute ?? route($isCommunity ? 'community.comments.store' : 'workshops.comments.store');
@endphp

<section {{ $attributes->merge(['class' => 'mt-12 rounded-[var(--radius-card)] border border-zinc-800/80 bg-surface-raised p-6 sm:p-8']) }} id="thao-luan">
    <h2 class="font-display text-xl font-semibold text-content-primary">
        Thảo luận &amp; góp ý
        <span class="text-base font-normal text-content-muted">({{ $commentCount }})</span>
    </h2>

    @auth
        <p class="mt-2 text-sm text-content-muted">
            Ý kiến của bạn sẽ hiển thị dưới tên tài khoản <strong class="font-medium text-content-primary">{{ auth()->user()->name }}</strong>.
        </p>
    @else
        <p class="mt-2 text-sm text-content-muted">
            @if ($isCommunity)
                Bạn không cần tài khoản. Tham gia thảo luận và trả lời bình luận của nhau.
            @else
                Bạn không cần tài khoản. Ý kiến giúp chúng tôi chọn chủ đề workshop phù hợp — có thể trả lời bình luận của nhau.
            @endif
        </p>
    @endauth

    <form
        method="POST"
        action="{{ $storeUrl }}"
        class="mt-8 flex flex-col gap-5 border-b border-zinc-800/80 pb-10"
        @guest x-data="guestNameForm" @submit="remember" @endguest
    >
        @csrf
        <input type="hidden" name="article_id" value="{{ $workshop->getKey() }}">

        <div class="hidden" aria-hidden="true">
            <label for="comment-website">Website</label>
            <input type="text" name="website" id="comment-website" tabindex="-1" autocomplete="off">
        </div>

        @guest
            <x-workshop.guest-name-field
                input-id="guest_name"
                :server-value="old('reply_to_id') ? '' : old('guest_name', '')"
                :show-errors="! old('reply_to_id')"
            />
        @endguest

        <div>
            <label for="body" class="mb-2 block text-sm font-medium text-content-primary">
                Ý kiến của bạn
            </label>
            <textarea
                name="body"
                id="body"
                rows="4"
                required
                maxlength="2000"
                class="w-full rounded-[var(--radius-button)] border border-zinc-700 bg-surface-base px-3 py-2.5 text-sm text-content-primary placeholder:text-content-muted focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                placeholder="{{ $isCommunity ? 'Chia sẻ suy nghĩ của bạn về bài viết này...' : 'Bạn mong muốn workshop chủ đề gì? Có gì cần lưu ý khi ghé makerspace?' }}"
            >{{ old('reply_to_id') ? '' : old('body') }}</textarea>
            @unless (old('reply_to_id'))
                @error('body')
                    <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                @enderror
            @endunless
        </div>

        <div class="pt-1">
            <x-ui.button variant="primary" type="submit">
                {{ $isCommunity ? 'Gửi bình luận' : 'Gửi góp ý' }}
            </x-ui.button>
        </div>
    </form>

    @if ($rootComments->isEmpty())
        <p class="mt-8 text-center text-sm text-content-muted">
            {{ $isCommunity ? 'Chưa có bình luận nào. Hãy là người đầu tiên!' : 'Chưa có góp ý nào. Hãy là người đầu tiên chia sẻ!' }}
        </p>
    @else
        <div class="mt-8 space-y-4">
            @foreach ($rootComments as $comment)
                <div class="rounded-xl border border-zinc-800/80 bg-surface-base/30 p-4 sm:p-5">
                    <x-workshop.comment-thread :comment="$comment" :workshop="$workshop" :store-route="$storeUrl" :reaction-context="$reactionContext" />
                </div>
            @endforeach
        </div>
    @endif
</section>
