@extends('layouts.app')

@section('title', 'Lịch workshop — Top3z')

@section('content')
    <section class="border-b border-zinc-800/80 bg-surface-raised/30 py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <x-site.section-heading
                title="Lịch workshop"
                subtitle="Workshop thực hành xây dựng & sáng tạo — cập nhật từ admin."
            />

            <nav class="mt-8 flex gap-2" aria-label="Lọc lịch">
                <a
                    href="{{ route('workshops.index', ['tab' => 'upcoming']) }}"
                    @class([
                        'rounded-[var(--radius-button)] px-4 py-2 text-sm font-semibold transition',
                        'bg-brand-500 text-zinc-950' => $tab === 'upcoming',
                        'border border-zinc-700 text-content-muted hover:bg-surface-raised hover:text-content-primary' => $tab !== 'upcoming',
                    ])
                >
                    Sắp diễn ra
                </a>
                <a
                    href="{{ route('workshops.index', ['tab' => 'past']) }}"
                    @class([
                        'rounded-[var(--radius-button)] px-4 py-2 text-sm font-semibold transition',
                        'bg-brand-500 text-zinc-950' => $tab === 'past',
                        'border border-zinc-700 text-content-muted hover:bg-surface-raised hover:text-content-primary' => $tab !== 'past',
                    ])
                >
                    Đã qua
                </a>
            </nav>
        </div>
    </section>

    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if ($workshops->isEmpty())
                <x-ui.card class="flex flex-col items-center py-12 text-center">
                    <p class="font-display text-lg font-semibold text-content-primary">
                        {{ $tab === 'upcoming' ? 'Chưa có workshop sắp tới' : 'Chưa có workshop đã qua' }}
                    </p>
                    <p class="mt-2 max-w-md text-sm text-content-muted">
                        Quay lại sau hoặc theo dõi trang chủ để cập nhật lịch mới.
                    </p>
                    <x-ui.button variant="secondary" :href="route('home')" class="mt-6">
                        Về trang chủ
                    </x-ui.button>
                </x-ui.card>
            @else
                <div class="space-y-4">
                    @foreach ($workshops as $workshop)
                        <x-workshop.timeline-item :workshop="$workshop" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $workshops->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
