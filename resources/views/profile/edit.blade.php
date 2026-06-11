@extends('layouts.app')

@section('title', 'Tài khoản — Top3z')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6">
        <h1 class="font-display text-3xl font-bold text-content-primary">Tài khoản</h1>
        <p class="mt-2 text-content-muted">Quản lý thông tin cá nhân và mật khẩu.</p>

        <div class="mt-8 space-y-6">
            <x-ui.card>
                @include('profile.partials.update-profile-information-form')
            </x-ui.card>

            <x-ui.card>
                @include('profile.partials.update-password-form')
            </x-ui.card>

            <x-ui.card>
                @include('profile.partials.delete-user-form')
            </x-ui.card>
        </div>
    </div>
@endsection
