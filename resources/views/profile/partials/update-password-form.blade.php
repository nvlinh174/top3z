<section>
    <h2 class="font-display text-lg font-semibold text-content-primary">Đổi mật khẩu</h2>
    <p class="mt-1 text-sm text-content-muted">Dùng mật khẩu mạnh, khó đoán.</p>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        <x-ui.form-field
            label="Mật khẩu hiện tại"
            name="current_password"
            type="password"
            required
            autocomplete="current-password"
        />

        <x-ui.form-field
            label="Mật khẩu mới"
            name="password"
            type="password"
            required
            autocomplete="new-password"
        />

        <x-ui.form-field
            label="Xác nhận mật khẩu mới"
            name="password_confirmation"
            type="password"
            required
            autocomplete="new-password"
        />

        <div class="flex items-center gap-4 pt-2">
            <x-ui.button type="submit">Cập nhật mật khẩu</x-ui.button>

            @if (session('status') === 'password-updated')
                <p class="text-sm text-success">Mật khẩu đã được cập nhật.</p>
            @endif
        </div>
    </form>
</section>
