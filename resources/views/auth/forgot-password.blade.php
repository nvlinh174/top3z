@extends('layouts.app')

@section('title', 'Quên mật khẩu — Top3z')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <x-ui.card>
            <h1 class="font-display text-2xl font-bold text-content-primary">Quên mật khẩu</h1>
            <p class="mt-2 text-sm text-content-muted">
                Nhập email đã đăng ký — chúng tôi sẽ gửi link đặt lại mật khẩu.
            </p>

            @if (session('status'))
                <p class="mt-4 rounded-[var(--radius-button)] border border-brand-500/30 bg-brand-500/10 px-4 py-3 text-sm text-brand-400">
                    {{ session('status') }}
                </p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                @csrf

                <x-ui.form-field
                    label="Email"
                    name="email"
                    type="email"
                    required
                    autofocus
                />

                <x-ui.button type="submit">
                    Gửi link đặt lại mật khẩu
                </x-ui.button>
            </form>

            <p class="mt-6 text-center text-sm text-content-muted">
                <a href="{{ route('login') }}" class="font-medium text-brand-400 hover:text-brand-300">Quay lại đăng nhập</a>
            </p>
        </x-ui.card>
    </div>
@endsection
