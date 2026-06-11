<footer class="border-t border-zinc-800/80 bg-surface-base">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-8 sm:flex-row sm:items-start sm:justify-between">
            <div class="max-w-sm">
                <p class="font-display text-lg font-bold text-content-primary">
                    Top<span class="text-brand-500">3z</span>
                </p>
                <p class="mt-2 text-sm text-content-muted">
                    Makerspace xây dựng và sáng tạo — workshop thực hành, cộng đồng chia sẻ trải nghiệm.
                </p>
            </div>

            <nav class="flex flex-col gap-2 text-sm" aria-label="Chân trang">
                <x-site.nav-link :href="route('home')">Trang chủ</x-site.nav-link>
                <x-site.nav-link :href="route('workshops.index')">Lịch workshop</x-site.nav-link>
                <x-site.nav-link :href="route('community.index')">Cộng đồng</x-site.nav-link>
            </nav>
        </div>

        <p class="mt-10 border-t border-zinc-800/80 pt-6 text-xs text-content-muted">
            © {{ date('Y') }} Top3z. Makerspace — build, thử, chia sẻ.
        </p>
    </div>
</footer>
