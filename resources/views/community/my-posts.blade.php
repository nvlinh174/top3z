@extends('layouts.app')

@section('title', 'Bài của tôi — Top3z')
@section('meta_description', 'Theo dõi bài viết cộng đồng bạn đã gửi tại Top3z.')

@section('content')
  @php
      $tabs = [
          'published' => ['label' => 'Đã đăng', 'count' => $publishedCount],
          'pending' => ['label' => 'Chờ duyệt', 'count' => $pendingCount],
          'rejected' => ['label' => 'Bị từ chối', 'count' => $rejectedCount],
      ];
  @endphp

  <section class="border-b border-zinc-800/80 bg-surface-raised/30 py-12 sm:py-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
        <x-site.section-heading
          title="Bài của tôi"
          subtitle="Theo dõi trạng thái bài viết bạn đã gửi lên cộng đồng."
        />

        <x-ui.button href="{{ route('community.create') }}" class="shrink-0">
          Viết bài mới
        </x-ui.button>
      </div>

      <nav class="mt-8 flex flex-wrap gap-2" aria-label="Trạng thái bài viết">
        @foreach ($tabs as $key => $tab)
          <a
            href="{{ route('community.my-posts', ['tab' => $key]) }}"
            @class([
              'inline-flex items-center gap-2 rounded-[var(--radius-button)] px-4 py-2 text-sm font-semibold transition',
              'bg-brand-500 text-zinc-950' => $activeTab === $key,
              'border border-zinc-700 text-content-muted hover:bg-surface-raised hover:text-content-primary' => $activeTab !== $key,
            ])
          >
            {{ $tab['label'] }}
            @if ($tab['count'] > 0)
              <span @class([
                'rounded-full px-2 py-0.5 text-xs',
                'bg-zinc-950/20' => $activeTab === $key,
                'bg-surface-overlay' => $activeTab !== $key,
              ])>{{ $tab['count'] }}</span>
            @endif
          </a>
        @endforeach
      </nav>
    </div>
  </section>

  <section class="py-12 sm:py-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
      @if ($posts->isEmpty())
        <x-ui.card class="flex flex-col items-center py-12 text-center">
          <p class="font-display text-lg font-semibold text-content-primary">
            @if ($activeTab === 'pending')
              Chưa có bài chờ duyệt
            @elseif ($activeTab === 'rejected')
              Không có bài bị từ chối
            @else
              Chưa có bài đã đăng
            @endif
          </p>
          <p class="mt-2 max-w-md text-sm text-content-muted">
            @if ($activeTab === 'published')
              Bài được duyệt sẽ hiện ở đây và trên trang Cộng đồng.
            @else
              Viết bài mới để chia sẻ trải nghiệm của bạn.
            @endif
          </p>
          <x-ui.button href="{{ route('community.create') }}" class="mt-6">
            Viết bài mới
          </x-ui.button>
        </x-ui.card>
      @else
        <div class="space-y-4">
          @foreach ($posts as $post)
            <x-ui.card class="flex flex-col gap-4 !p-5 sm:flex-row sm:items-center sm:justify-between">
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <x-community.moderation-badge :status="$post->moderation_status" />
                  @if ($post->submitted_at)
                    <time class="text-xs text-content-muted" datetime="{{ $post->submitted_at->toIso8601String() }}">
                      Gửi {{ $post->submitted_at->diffForHumans() }}
                    </time>
                  @endif
                </div>

                <h2 class="mt-2 font-display text-lg font-semibold text-content-primary">
                  <a href="{{ route('community.show', $post) }}" class="hover:text-brand-400">
                    {{ $post->title }}
                  </a>
                </h2>

                @if ($post->excerpt)
                  <p class="mt-1 line-clamp-2 text-sm text-content-muted">{{ $post->excerpt }}</p>
                @endif

                @if ($post->moderation_status === \App\Enums\ArticleModerationStatus::Rejected && $post->moderation_note)
                  <p class="mt-2 text-sm text-red-300/90">{{ $post->moderation_note }}</p>
                @endif
              </div>

              <div class="flex shrink-0 flex-wrap gap-2">
                <x-ui.button variant="secondary" href="{{ route('community.show', $post) }}">
                  Xem
                </x-ui.button>
                <x-ui.button variant="ghost" href="{{ route('community.edit', $post) }}">
                  Sửa
                </x-ui.button>
              </div>
            </x-ui.card>
          @endforeach
        </div>

        <div class="mt-10">
          {{ $posts->links() }}
        </div>
      @endif
    </div>
  </section>
@endsection
