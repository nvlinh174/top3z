@extends('layouts.app')

@section('title', $member->name.' — Cộng đồng Top3z')
@section('meta_description', 'Bài viết cộng đồng của '.$member->name.' trên Top3z makerspace.')

@section('content')
    <section class="border-b border-zinc-800/80 bg-surface-raised/30 py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center gap-6 text-center sm:flex-row sm:text-left">
                <x-user.avatar :user="$member" size="lg" class="ring-2 ring-brand-500/30" />

                <div>
                    <h1 class="font-display text-3xl font-bold tracking-tight text-content-primary sm:text-4xl">
                        {{ $member->name }}
                    </h1>
                    <p class="mt-2 text-content-muted">
                        {{ $member->public_community_posts_count }}
                        {{ $member->public_community_posts_count === 1 ? 'bài viết' : 'bài viết' }}
                        trên cộng đồng
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if ($posts->isEmpty())
                <x-ui.card class="flex flex-col items-center py-12 text-center">
                    <p class="font-display text-lg font-semibold text-content-primary">
                        Chưa có bài công khai
                    </p>
                    <p class="mt-2 max-w-md text-sm text-content-muted">
                        Thành viên này chưa đăng bài nào được duyệt trên cộng đồng.
                    </p>
                    <x-ui.button variant="secondary" :href="route('community.index')" class="mt-6">
                        Về cộng đồng
                    </x-ui.button>
                </x-ui.card>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <x-community.post-card :post="$post" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
