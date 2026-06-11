@props([
    'label',
    'name',
    'type' => 'text',
    'id' => null,
    'required' => false,
    'autocomplete' => null,
    'value' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    <label for="{{ $inputId }}" class="mb-2 block text-sm font-medium text-content-primary">
        {{ $label }}
    </label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->except('class') }}
        class="w-full rounded-[var(--radius-button)] border border-zinc-700 bg-surface-base px-3 py-2.5 text-sm text-content-primary placeholder:text-content-muted focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
    >
    @error($name)
        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
    @enderror
</div>
