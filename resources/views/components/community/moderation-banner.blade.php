@props([
    'post',
])

@php
    /** @var \App\Models\Article $post */
@endphp

@if ($post->moderation_status === \App\Enums\ArticleModerationStatus::Pending)
    <x-ui.card class="!border-amber-500/30 !bg-amber-500/10 !p-4">
        <p class="text-sm font-semibold text-amber-200">Bài đang chờ duyệt</p>
        <p class="mt-1 text-sm text-amber-200/80">
            Chỉ bạn và admin xem được trang này. Bài sẽ hiện trên Cộng đồng sau khi được duyệt.
        </p>
    </x-ui.card>
@elseif ($post->moderation_status === \App\Enums\ArticleModerationStatus::Rejected)
    <x-ui.card class="!border-red-500/30 !bg-red-500/10 !p-4">
        <p class="text-sm font-semibold text-red-200">Bài bị từ chối</p>
        @if ($post->moderation_note)
            <p class="mt-1 text-sm text-red-200/80">{{ $post->moderation_note }}</p>
        @endif
        <p class="mt-2 text-sm text-red-200/80">
            Bạn có thể chỉnh sửa và gửi lại để duyệt.
        </p>
    </x-ui.card>
@endif
