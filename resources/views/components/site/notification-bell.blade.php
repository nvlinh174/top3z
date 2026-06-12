@props([
    'class' => '',
])

<div
    {{ $attributes->merge(['class' => 'relative '.$class]) }}
    x-data="notificationBell({
        recentUrl: @js(route('notifications.recent')),
        unreadCountUrl: @js(route('notifications.unread-count')),
        readAllUrl: @js(route('notifications.read-all')),
        readUrlTemplate: @js(route('notifications.read', ['id' => '__ID__'])),
        indexUrl: @js(route('notifications.index')),
    })"
    x-on:click.outside="open = false"
>
    <button
        type="button"
        class="relative inline-flex items-center justify-center rounded-[var(--radius-button)] p-2 text-content-muted hover:bg-surface-raised hover:text-content-primary"
        x-on:click="toggle()"
        aria-label="Thông báo"
        aria-haspopup="true"
        x-bind:aria-expanded="open.toString()"
    >
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        <span
            x-show="count > 0"
            x-cloak
            x-text="count > 9 ? '9+' : count"
            class="absolute -right-0.5 -top-0.5 inline-flex min-w-[1.125rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-4 text-white"
        ></span>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="absolute z-50 mt-2 max-h-[min(24rem,calc(100dvh-6rem))] w-[min(100vw-2rem,24rem)] overflow-hidden rounded-[var(--radius-button)] border border-zinc-800 bg-surface-raised shadow-lg max-sm:fixed max-sm:inset-x-4 max-sm:right-auto max-sm:top-[calc(3.5rem+var(--site-safe-top))] sm:right-0 sm:w-96"
    >
        <div class="flex items-center justify-between border-b border-zinc-800 px-4 py-3">
            <p class="text-sm font-semibold text-content-primary">Thông báo</p>
            <button
                type="button"
                class="text-xs font-medium text-brand-500 hover:text-brand-400 disabled:opacity-50"
                x-on:click="markAllRead()"
                x-bind:disabled="count === 0 || loading"
            >
                Đánh dấu tất cả đã đọc
            </button>
        </div>

        <div class="max-h-96 overflow-y-auto">
            <template x-if="loading && items.length === 0">
                <p class="px-4 py-6 text-center text-sm text-content-muted">Đang tải…</p>
            </template>

            <template x-if="! loading && items.length === 0">
                <p class="px-4 py-6 text-center text-sm text-content-muted">Chưa có thông báo</p>
            </template>

            <template x-for="item in items" :key="item.id">
                <button
                    type="button"
                    class="flex w-full items-start gap-3 border-b border-zinc-800/60 px-4 py-3 text-left hover:bg-surface-overlay"
                    x-on:click="openItem(item)"
                >
                    <span
                        class="mt-1.5 size-2 shrink-0 rounded-full"
                        x-bind:class="item.read_at ? 'bg-transparent' : 'bg-brand-500'"
                    ></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm text-content-primary" x-text="item.message"></span>
                        <span class="mt-1 block text-xs text-content-muted" x-text="item.created_at_human"></span>
                    </span>
                </button>
            </template>
        </div>

        <div class="border-t border-zinc-800 px-4 py-2">
            <a
                :href="indexUrl"
                class="block py-2 text-center text-sm font-medium text-brand-500 hover:text-brand-400"
            >
                Xem tất cả
            </a>
        </div>
    </div>
</div>
