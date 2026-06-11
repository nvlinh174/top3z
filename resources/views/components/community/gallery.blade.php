@props([
    'post',
])

@php
    /** @var \App\Models\Article $post */
    $galleryItems = $post->getMedia('gallery');
@endphp

@if ($galleryItems->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-3 sm:grid-cols-3']) }}>
        @foreach ($galleryItems as $media)
            <img
                src="{{ $media->getUrl('large') }}"
                alt=""
                class="aspect-square rounded-lg object-cover"
                loading="lazy"
            >
        @endforeach
    </div>
@endif
