@extends('layouts.app')

@section('title', 'Xác minh email — Top3z')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <x-ui.card>
            <h1 class="font-display text-2xl font-bold text-content-primary">Xác minh email</h1>
            <p class="mt-2 text-sm text-content-muted">
                Cảm ơn bạn đã đăng ký! Vui lòng kiểm tra hộp thư và bấm link xác minh.
            </p>

            @if (session('status') === 'verification-link-sent')
                <p class="mt-4 rounded-[var(--radius-button)] border border-brand-500/30 bg-brand-500/10 px-4 py-3 text-sm text-brand-400">
                    Link xác minh mới đã được gửi tới email của bạn.
                </p>
            @endif

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-ui.button type="submit">
                        Gửi lại email xác minh
                    </x-ui.button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button type="submit" variant="ghost">
                        Đăng xuất
                    </x-ui.button>
                </form>
            </div>
        </x-ui.card>
    </div>
@endsection
