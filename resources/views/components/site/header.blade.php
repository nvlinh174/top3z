<header
    class="site-header sticky top-0 z-50 border-b border-zinc-800/80 bg-surface-base/90 backdrop-blur-md"
    style="padding-top: var(--site-safe-top);"
    x-data="{ open: false, userMenu: false }"
>
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6 sm:py-4 lg:px-8">
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

        <div class="hidden items-center gap-3 md:flex">
            @auth
                <x-ui.button variant="secondary" href="{{ route('community.create') }}">
                    Viết bài mới
                </x-ui.button>

                <x-site.notification-bell />

                <div class="relative" x-on:click.outside="userMenu = false">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-[var(--radius-button)] px-3 py-2 text-sm font-medium text-content-primary hover:bg-surface-raised"
                        x-on:click="userMenu = ! userMenu"
                        aria-haspopup="true"
                        x-bind:aria-expanded="userMenu.toString()"
                    >
                        <x-user.avatar :user="auth()->user()" size="sm" />
                        <span class="max-w-[8rem] truncate">{{ auth()->user()->name }}</span>
                        <svg class="size-4 text-content-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="userMenu"
                        x-cloak
                        x-transition
                        class="absolute right-0 z-50 mt-2 w-48 rounded-[var(--radius-button)] border border-zinc-800 bg-surface-raised py-1 shadow-lg"
                    >
                        <a href="{{ route('community.create') }}" class="block px-4 py-2 text-sm text-content-primary hover:bg-surface-overlay">
                            Viết bài mới
                        </a>
                        <a href="{{ route('community.my-posts') }}" class="block px-4 py-2 text-sm text-content-primary hover:bg-surface-overlay">
                            Bài của tôi
                        </a>
                        <a href="{{ route('community.saved') }}" class="block px-4 py-2 text-sm text-content-primary hover:bg-surface-overlay">
                            Bài đã lưu
                        </a>
                        <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm text-content-primary hover:bg-surface-overlay">
                            Thông báo
                        </a>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-content-primary hover:bg-surface-overlay">
                            Tài khoản
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-content-muted hover:bg-surface-overlay hover:text-content-primary">
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <x-ui.button variant="ghost" href="{{ route('login') }}">
                    Đăng nhập
                </x-ui.button>
                <x-ui.button href="{{ route('register') }}">
                    Đăng ký
                </x-ui.button>
            @endauth
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

            <div class="mt-3 border-t border-zinc-800/80 pt-3">
                @auth
                    <div class="flex items-center justify-between gap-3 px-3 py-2">
                        <div class="flex items-center gap-3">
                            <x-user.avatar :user="auth()->user()" size="sm" />
                            <p class="text-sm font-medium text-content-primary">{{ auth()->user()->name }}</p>
                        </div>
                        <x-site.notification-bell />
                    </div>
                    <x-site.nav-link :href="route('community.create')" :active="request()->routeIs('community.create')" class="rounded-lg px-3 py-2 hover:bg-surface-raised">
                        Viết bài mới
                    </x-site.nav-link>
                    <x-site.nav-link :href="route('community.my-posts')" :active="request()->routeIs('community.my-posts')" class="rounded-lg px-3 py-2 hover:bg-surface-raised">
                        Bài của tôi
                    </x-site.nav-link>
                    <x-site.nav-link :href="route('community.saved')" :active="request()->routeIs('community.saved')" class="rounded-lg px-3 py-2 hover:bg-surface-raised">
                        Bài đã lưu
                    </x-site.nav-link>
                    <x-site.nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')" class="rounded-lg px-3 py-2 hover:bg-surface-raised">
                        Thông báo
                    </x-site.nav-link>
                    <x-site.nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" class="rounded-lg px-3 py-2 hover:bg-surface-raised">
                        Tài khoản
                    </x-site.nav-link>
                    <form method="POST" action="{{ route('logout') }}" class="px-3 py-2">
                        @csrf
                        <button type="submit" class="text-sm text-content-muted hover:text-content-primary">
                            Đăng xuất
                        </button>
                    </form>
                @else
                    <div class="flex flex-col gap-2 px-3">
                        <x-ui.button variant="secondary" href="{{ route('login') }}" class="w-full justify-center">
                            Đăng nhập
                        </x-ui.button>
                        <x-ui.button href="{{ route('register') }}" class="w-full justify-center">
                            Đăng ký
                        </x-ui.button>
                    </div>
                @endauth
            </div>
        </nav>
    </div>
</header>
