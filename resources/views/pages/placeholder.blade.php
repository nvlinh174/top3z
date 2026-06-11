@extends('layouts.app')

@section('title', $title.' — Top3z')

@section('content')
    <section class="py-20 sm:py-28">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="font-display text-3xl font-bold text-content-primary sm:text-4xl">
                {{ $heading }}
            </h1>
            <p class="mt-4 text-content-muted">
                {{ $message }}
            </p>
            <x-ui.button variant="primary" :href="route('home')" class="mt-8">
                Về trang chủ
            </x-ui.button>
        </div>
    </section>
@endsection
