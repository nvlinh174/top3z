<section x-data="{ confirmDelete: false }">
    <h2 class="font-display text-lg font-semibold text-content-primary">Xóa tài khoản</h2>
    <p class="mt-1 text-sm text-content-muted">
        Hành động này không thể hoàn tác. Toàn bộ dữ liệu tài khoản sẽ bị xóa vĩnh viễn.
    </p>

    <x-ui.button
        type="button"
        variant="secondary"
        class="mt-4 border-red-500/40 text-red-400 hover:border-red-500/60 hover:bg-red-500/10"
        x-on:click="confirmDelete = true"
    >
        Xóa tài khoản
    </x-ui.button>

    <div
        x-show="confirmDelete"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/80 p-4"
        x-on:keydown.escape.window="confirmDelete = false"
    >
        <div class="w-full max-w-md rounded-[var(--radius-card)] border border-zinc-800 bg-surface-raised p-6 shadow-xl">
            <h3 class="font-display text-lg font-semibold text-content-primary">Xác nhận xóa tài khoản?</h3>
            <p class="mt-2 text-sm text-content-muted">
                Nhập mật khẩu để xác nhận. Bạn sẽ bị đăng xuất ngay lập tức.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-6 space-y-4">
                @csrf
                @method('delete')

                <x-ui.form-field
                    label="Mật khẩu"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                />

                <div class="flex gap-3">
                    <x-ui.button type="button" variant="ghost" x-on:click="confirmDelete = false">
                        Hủy
                    </x-ui.button>
                    <x-ui.button
                        type="submit"
                        class="bg-red-600 text-white hover:bg-red-500"
                    >
                        Xóa vĩnh viễn
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</section>
