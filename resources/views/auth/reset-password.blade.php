@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu — Top3z')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <x-ui.card>
            <h1 class="font-display text-2xl font-bold text-content-primary">Đặt lại mật khẩu</h1>

            <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <x-ui.form-field
                    label="Email"
                    name="email"
                    type="email"
                    :value="old('email', $request->email)"
                    required
                    autocomplete="username"
                    autofocus
                />

                <x-ui.form-field
                    label="Mật khẩu mới"
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

                <x-ui.button type="submit">
                    Đặt lại mật khẩu
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>
@endsection
