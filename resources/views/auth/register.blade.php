@extends('layouts.app')

@section('title', 'Đăng ký — Top3z')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <x-ui.card>
            <h1 class="font-display text-2xl font-bold text-content-primary">Đăng ký</h1>
            <p class="mt-2 text-sm text-content-muted">Tạo tài khoản để theo dõi workshop và tham gia cộng đồng.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                @csrf

                <x-ui.form-field
                    label="Họ tên"
                    name="name"
                    type="text"
                    required
                    autocomplete="name"
                    autofocus
                />

                <x-ui.form-field
                    label="Email"
                    name="email"
                    type="email"
                    required
                    autocomplete="username"
                />

                <x-ui.form-field
                    label="Mật khẩu"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                />

                <x-ui.form-field
                    label="Xác nhận mật khẩu"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                />

                <div class="pt-2">
                    <x-ui.button type="submit" class="w-full sm:w-auto">
                        Đăng ký
                    </x-ui.button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-content-muted">
                Đã có tài khoản?
                <a href="{{ route('login') }}" class="font-medium text-brand-400 hover:text-brand-300">Đăng nhập</a>
            </p>
        </x-ui.card>
    </div>
@endsection
