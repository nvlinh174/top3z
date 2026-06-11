@extends('layouts.app')

@section('title', 'Xác nhận mật khẩu — Top3z')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <x-ui.card>
            <h1 class="font-display text-2xl font-bold text-content-primary">Xác nhận mật khẩu</h1>
            <p class="mt-2 text-sm text-content-muted">
                Vui lòng nhập mật khẩu để tiếp tục thao tác nhạy cảm.
            </p>

            <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
                @csrf

                <x-ui.form-field
                    label="Mật khẩu"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    autofocus
                />

                <x-ui.button type="submit">
                    Xác nhận
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>
@endsection
