<header
    class="sticky top-0 z-50 border-b border-zinc-800/80 bg-surface-base/90 backdrop-blur-md"
    x-data="{ open: false }"
>
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="font-display text-lg font-bold tracking-tight text-content-primary">
            Top<span class="text-brand-500">3z</span>
        </a>

        <nav class="hidden items-center gap-8 md:flex" aria-label="Chính">
            <x-site.nav-link :href="route('home')" :active="request()->routeIs('home')">
                Trang chủ
            </x-site.nav-link>
            <x-site.nav-link :href="route('workshops.index')" :active="request()->routeIs('workshops.*')">
                Lịch workshop
            </x-site.nav-link>
            <x-site.nav-link :href="route('community.index')" :active="request()->routeIs('community.*')">
                Cộng đồng
            </x-site.nav-link>
        </nav>

        <div class="hidden md:block">
            <x-ui.button variant="ghost" href="{{ route('community.index') }}">
                Khám phá
            </x-ui.button>
        </div>

        <button
            type="button"
            class="inline-flex items-center justify-center rounded-[var(--radius-button)] p-2 text-content-muted hover:bg-surface-raised hover:text-content-primary md:hidden"
            aria-label="Mở menu"
            aria-expanded="false"
            x-bind:aria-expanded="open.toString()"
            x-on:click="open = ! open"
        >
            <svg x-show="! open" class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <svg x-show="open" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="border-t border-zinc-800/80 md:hidden"
    >
        <nav class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-4 sm:px-6" aria-label="Di động">
            <x-site.nav-link :href="route('home')" :active="request()->routeIs('home')" class="rounded-lg px-3 py-2 hover:bg-surface-raised">
                Trang chủ
            </x-site.nav-link>
            <x-site.nav-link :href="route('workshops.index')" :active="request()->routeIs('workshops.*')" class="rounded-lg px-3 py-2 hover:bg-surface-raised">
                Lịch workshop
            </x-site.nav-link>
            <x-site.nav-link :href="route('community.index')" :active="request()->routeIs('community.*')" class="rounded-lg px-3 py-2 hover:bg-surface-raised">
                Cộng đồng
            </x-site.nav-link>
        </nav>
    </div>
</header>
