<section>
    <h2 class="font-display text-lg font-semibold text-content-primary">Thông tin cá nhân</h2>
    <p class="mt-1 text-sm text-content-muted">Cập nhật họ tên hiển thị trên Top3z.</p>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('patch')

        <x-ui.form-field
            label="Họ tên"
            name="name"
            type="text"
            :value="$user->name"
            required
            autocomplete="name"
        />

        <div>
            <p class="mb-2 block text-sm font-medium text-content-primary">Email</p>
            <p class="rounded-[var(--radius-button)] border border-zinc-800 bg-surface-base/50 px-3 py-2.5 text-sm text-content-muted">
                {{ $user->email }}
            </p>
            <p class="mt-2 text-xs text-content-muted">
                Email đăng nhập không thể đổi. Cần đổi email, vui lòng liên hệ quản trị viên.
            </p>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-ui.button type="submit">Lưu thay đổi</x-ui.button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-success"
                >
                    Đã lưu.
                </p>
            @endif
        </div>
    </form>
</section>
