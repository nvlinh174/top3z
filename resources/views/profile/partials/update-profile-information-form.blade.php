<section>
    <h2 class="font-display text-lg font-semibold text-content-primary">Thông tin cá nhân</h2>
    <p class="mt-1 text-sm text-content-muted">Cập nhật họ tên và ảnh đại diện hiển thị trên Top3z.</p>

    <form
        method="post"
        action="{{ route('profile.update') }}"
        enctype="multipart/form-data"
        class="mt-6 space-y-4"
        x-data="{ preview: @js($user->avatarUrl('thumb')) }"
    >
        @csrf
        @method('patch')

        <div>
            <p class="mb-2 block text-sm font-medium text-content-primary">Ảnh đại diện</p>
            <div class="flex items-center gap-4">
                <template x-if="preview">
                    <img
                        :src="preview"
                        alt=""
                        class="size-16 rounded-full object-cover ring-2 ring-brand-500/30"
                    >
                </template>
                <template x-if="! preview">
                    <x-user.avatar :user="$user" size="lg" class="ring-2 ring-brand-500/30" />
                </template>

                <div class="min-w-0 flex-1">
                    <input
                        type="file"
                        name="avatar"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        class="block w-full text-sm text-content-muted file:mr-3 file:rounded-[var(--radius-button)] file:border-0 file:bg-brand-500 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-zinc-950 hover:file:bg-brand-400"
                        x-on:change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview"
                    >
                    <p class="mt-2 text-xs text-content-muted">JPG, PNG hoặc WebP — tối đa 2MB.</p>
                </div>
            </div>
            @error('avatar')
                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

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
