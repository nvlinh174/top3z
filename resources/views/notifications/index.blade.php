@extends('layouts.app')

@section('title', 'Thông báo — Top3z')
@section('meta_description', 'Xem thông báo về bài viết, bình luận và tương tác cộng đồng tại Top3z.')

@section('content')
    <section class="border-b border-zinc-800/80 bg-surface-raised/30 py-12 sm:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <x-site.section-heading
                    title="Thông báo"
                    subtitle="Cập nhật về bài viết, bình luận và tương tác của bạn."
                />

                @if (auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}" class="shrink-0">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">
                            Đánh dấu tất cả đã đọc
                        </x-ui.button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="py-10 sm:py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if ($notifications->isEmpty())
                <x-ui.card class="p-8 text-center">
                    <p class="text-content-muted">Chưa có thông báo nào.</p>
                </x-ui.card>
            @else
                <div class="divide-y divide-zinc-800 overflow-hidden rounded-[var(--radius-button)] border border-zinc-800 bg-surface-raised">
                    @foreach ($notifications as $notification)
                        @php
                            $isUnread = $notification->read_at === null;
                            $url = $notification->data['url'] ?? route('notifications.index');
                        @endphp

                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="block">
                            @csrf
                            <button
                                type="submit"
                                @class([
                                    'flex w-full items-start gap-3 px-4 py-4 text-left transition hover:bg-surface-overlay',
                                    'bg-surface-overlay/40' => $isUnread,
                                ])
                            >
                                <span @class([
                                    'mt-2 size-2 shrink-0 rounded-full',
                                    'bg-brand-500' => $isUnread,
                                    'bg-transparent' => ! $isUnread,
                                ])></span>

                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm text-content-primary">
                                        {{ $notification->data['message'] ?? '' }}
                                    </span>
                                    <span class="mt-1 block text-xs text-content-muted">
                                        {{ $notification->created_at?->diffForHumans() }}
                                    </span>
                                </span>
                            </button>
                        </form>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
