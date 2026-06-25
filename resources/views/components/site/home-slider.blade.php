@props([
    'slides',
])

@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\HomeSlide> $slides */
@endphp

@if ($slides->isNotEmpty())
    <section
        class="border-b border-zinc-800/80 bg-surface-base"
        aria-label="Slider trang chủ"
        x-data="homeSlider({ count: {{ $slides->count() }} })"
        x-init="start()"
        @mouseenter="pause()"
        @mouseleave="resume()"
    >
        <div class="relative overflow-hidden">
            @foreach ($slides as $slide)
                <div
                    class="transition-opacity duration-500"
                    x-show="active === {{ $loop->index }}"
                    x-transition:enter="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @if (! $loop->first) style="display: none;" @endif
                >
                    <img
                        src="{{ $slide->imageUrl() }}"
                        alt=""
                        class="aspect-[16/5] w-full object-cover sm:aspect-[32/9]"
                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                        decoding="async"
                    >
                </div>
            @endforeach

            @if ($slides->count() > 1)
                <div class="absolute inset-x-0 bottom-3 flex justify-center gap-2">
                    @foreach ($slides as $slide)
                        <button
                            type="button"
                            class="size-2 rounded-full transition"
                            :class="active === {{ $loop->index }} ? 'bg-white' : 'bg-white/40 hover:bg-white/70'"
                            aria-label="Slide {{ $loop->iteration }}"
                            @click="goTo({{ $loop->index }})"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
