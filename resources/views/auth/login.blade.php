@extends('layouts.app')

@section('title', 'Đăng nhập — Top3z')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <x-ui.card>
            <h1 class="font-display text-2xl font-bold text-content-primary">Đăng nhập</h1>
            <p class="mt-2 text-sm text-content-muted">Chào mừng trở lại Top3z makerspace.</p>

            @if (session('status'))
                <p class="mt-4 rounded-[var(--radius-button)] border border-brand-500/30 bg-brand-500/10 px-4 py-3 text-sm text-brand-400">
                    {{ session('status') }}
                </p>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf

                <x-ui.form-field
                    label="Email"
                    name="email"
                    type="email"
                    required
                    autocomplete="username"
                    autofocus
                />

                <x-ui.form-field
                    label="Mật khẩu"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                />

                <label class="flex items-center gap-2 text-sm text-content-muted">
                    <input
                        type="checkbox"
                        name="remember"
                        class="rounded border-zinc-700 bg-surface-base text-brand-500 focus:ring-brand-500"
                    >
                    Ghi nhớ đăng nhập
                </label>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-brand-400 hover:text-brand-300">
                            Quên mật khẩu?
                        </a>
                    @endif

                    <x-ui.button type="submit" class="sm:ms-auto">
                        Đăng nhập
                    </x-ui.button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-content-muted">
                Chưa có tài khoản?
                <a href="{{ route('register') }}" class="font-medium text-brand-400 hover:text-brand-300">Đăng ký</a>
            </p>
        </x-ui.card>
    </div>
@endsection
