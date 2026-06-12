@props([
    'items' => [],
])

@php
    /**
     * @var list<array{label: string, href?: string|null}> $items
     */
@endphp

<nav {{ $attributes->merge(['class' => 'text-sm text-content-muted']) }} aria-label="Breadcrumb">
    <ol class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1">
        @foreach ($items as $index => $item)
            @if ($index > 0)
                <li class="text-content-muted/60" aria-hidden="true">/</li>
            @endif
            <li @class(['min-w-0', 'max-w-full' => $loop->last])>
                @if (! empty($item['href']) && ! $loop->last)
                    <a href="{{ $item['href'] }}" class="hover:text-brand-400">{{ $item['label'] }}</a>
                @else
                    <span @class(['block truncate text-content-primary' => $loop->last]) title="{{ $item['label'] }}">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
