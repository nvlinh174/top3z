@extends('layouts.app')

@section('title', 'Viết bài mới — Cộng đồng Top3z')

@section('content')
    <div class="border-b border-zinc-800/80 bg-surface-raised/20 py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-content-muted">
                <a href="{{ route('community.index') }}" class="hover:text-brand-400">Cộng đồng</a>
                <span class="mx-2">/</span>
                <span class="text-content-primary">Viết bài mới</span>
            </nav>
            <h1 class="mt-2 font-display text-2xl font-bold text-content-primary sm:text-3xl">Soạn bài viết</h1>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        @include('community._form', [
            'post' => $draftPost,
            'bodyHtml' => old('body', $draftPost?->body ?? ''),
            'action' => $draftPost ? route('community.update', $draftPost) : route('community.store'),
            'method' => $draftPost ? 'PATCH' : 'POST',
            'enableDraft' => true,
            'draftAutosaveUrl' => $draftPost ? route('community.drafts.autosave', $draftPost) : null,
            'draftDestroyUrl' => $draftPost ? route('community.drafts.destroy', $draftPost) : null,
            'latestDraftEditUrl' => $latestDraft ? route('community.edit', $latestDraft) : null,
        ])
    </div>
@endsection
