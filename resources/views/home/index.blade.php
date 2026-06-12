@extends('layouts.app')

@section('title', 'Top3z — Makerspace xây dựng & sáng tạo')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-zinc-800/80 bg-blueprint">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-brand-500/5 via-transparent to-surface-base"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <x-ui.badge variant="upcoming" class="mb-6">
                Makerspace · Workshop hàng tháng
            </x-ui.badge>

            <h1 class="max-w-3xl font-display text-4xl font-extrabold tracking-tight text-content-primary sm:text-5xl lg:text-6xl">
                Nơi bạn <span class="text-brand-500">build</span>, thử và chia sẻ
            </h1>

            <p class="mt-6 max-w-2xl text-lg text-content-muted">
                Workshop thực hành xây dựng &amp; sáng tạo sản phẩm — tham gia không cần đăng ký tài khoản trước.
            </p>

            <div class="mt-10 flex flex-wrap gap-4">
                <x-ui.button variant="primary" :href="route('workshops.index')">
                    Xem lịch workshop
                </x-ui.button>
                <x-ui.button variant="secondary" :href="route('community.index')">
                    Khám phá cộng đồng
                </x-ui.button>
            </div>

            @if ($featuredWorkshop)
                <div class="mt-10 max-w-xl rounded-[var(--radius-card)] border border-brand-500/30 bg-surface-raised/80 p-5 backdrop-blur-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-400">Workshop sắp tới</p>
                    <p class="mt-2 font-display text-lg font-semibold text-content-primary">
                        <a href="{{ route('workshops.show', $featuredWorkshop) }}" class="hover:text-brand-400">
                            {{ $featuredWorkshop->title }}
                        </a>
                    </p>
                    @if ($featuredWorkshop->getFormattedSchedule())
                        <p class="mt-1 font-mono text-sm text-content-muted">{{ $featuredWorkshop->getFormattedSchedule() }}</p>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- Stats strip --}}
    <section class="border-b border-zinc-800/80 bg-surface-raised/50">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <p class="text-center text-sm font-medium text-content-muted">
                Makerspace mở cửa — workshop thực hành, góp ý và vote tham gia không cần tài khoản
            </p>
        </div>
    </section>

    {{-- Upcoming workshops --}}
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-6xl space-y-10 px-4 sm:px-6 lg:px-8">
            <x-site.section-heading
                title="Workshop sắp tới"
                subtitle="Lịch sự kiện từ admin — cập nhật thường xuyên."
            />

            @if ($upcomingWorkshops->isEmpty() && ! $featuredWorkshop)
                <x-ui.card class="flex flex-col items-center py-12 text-center">
                    <div class="flex size-14 items-center justify-center rounded-full bg-brand-500/10 text-brand-400">
                        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <p class="mt-4 font-display text-lg font-semibold text-content-primary">
                        Chưa có workshop trên lịch
                    </p>
                    <p class="mt-2 max-w-md text-sm text-content-muted">
                        Theo dõi trang lịch hoặc quay lại sau — buổi tiếp theo sẽ được cập nhật từ admin.
                    </p>
                    <x-ui.button variant="secondary" :href="route('workshops.index')" class="mt-6">
                        Đến trang lịch
                    </x-ui.button>
                </x-ui.card>
            @else
                <div class="space-y-4">
                    @foreach ($upcomingWorkshops as $workshop)
                        <x-workshop.timeline-item :workshop="$workshop" />
                    @endforeach
                </div>

                <div class="text-center">
                    <x-ui.button variant="ghost" :href="route('workshops.index')" class="mt-6">
                        Xem toàn bộ lịch →
                    </x-ui.button>
                </div>
            @endif
        </div>
    </section>

    {{-- Community preview --}}
    <section class="border-t border-zinc-800/80 bg-surface-raised/30 py-16 sm:py-24">
        <div class="mx-auto max-w-6xl space-y-10 px-4 sm:px-6 lg:px-8">
            <x-site.section-heading
                title="Từ cộng đồng"
                subtitle="Chia sẻ trải nghiệm và ảnh sản phẩm sau workshop."
            />

            @if ($communityPosts->isEmpty())
                <x-ui.card class="py-10 text-center">
                    <p class="text-sm text-content-muted">Chưa có bài chia sẻ — quay lại sau nhé.</p>
                </x-ui.card>
            @else
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($communityPosts as $post)
                        <x-community.post-card :post="$post" />
                    @endforeach
                </div>
            @endif

            <div class="text-center">
                <x-ui.button variant="ghost" :href="route('community.index')">
                    Xem cộng đồng →
                </x-ui.button>
            </div>
        </div>
    </section>
@endsection
