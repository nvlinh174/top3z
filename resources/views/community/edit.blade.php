@extends('layouts.app')

@section('title', 'Sửa bài — '.$post->title)

@section('content')
    <div class="border-b border-zinc-800/80 bg-surface-raised/20 py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-content-muted">
                <a href="{{ route('community.index') }}" class="hover:text-brand-400">Cộng đồng</a>
                <span class="mx-2">/</span>
                <a href="{{ route('community.show', $post) }}" class="hover:text-brand-400">{{ $post->title }}</a>
                <span class="mx-2">/</span>
                <span class="text-content-primary">Sửa bài</span>
            </nav>
            <h1 class="mt-2 font-display text-2xl font-bold text-content-primary sm:text-3xl">Sửa bài viết</h1>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        @include('community._form', [
            'post' => $post,
            'bodyHtml' => $bodyHtml,
            'action' => route('community.update', $post),
            'method' => 'PATCH',
        ])
    </div>
@endsection
