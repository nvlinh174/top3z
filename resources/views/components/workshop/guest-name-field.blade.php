@props([
    'inputId',
    'serverValue' => '',
    'showErrors' => false,
    'context' => 'comment',
])

@php
    $summaryPrefix = $context === 'reply' ? 'Trả lời với tên' : 'Góp ý với tên';
@endphp

<input type="hidden" data-server-guest-name value="{{ $serverValue }}">

<div x-show="ready" x-cloak>
    <template x-if="! editingName && storedName">
        <div>
            <p class="text-sm text-content-muted">
                {{ $summaryPrefix }}
                <strong class="font-medium text-content-primary" x-text="storedName"></strong>
                <span class="mx-1" aria-hidden="true">·</span>
                <button
                    type="button"
                    class="cursor-pointer text-brand-400 underline-offset-2 hover:text-brand-300 hover:underline"
                    @click="startEditing()"
                >
                    Đổi tên
                </button>
            </p>
            <input type="hidden" name="guest_name" :value="storedName">
        </div>
    </template>

    <template x-if="editingName || ! storedName">
        <div>
            <label for="{{ $inputId }}" class="mb-2 block text-sm font-medium text-content-primary">
                Tên <span class="font-normal text-content-muted">(tuỳ chọn)</span>
            </label>
            <input
                type="text"
                name="guest_name"
                id="{{ $inputId }}"
                data-guest-name
                x-model="draftName"
                maxlength="100"
                class="w-full rounded-[var(--radius-button)] border border-zinc-700 bg-surface-base px-3 py-2.5 text-sm text-content-primary placeholder:text-content-muted focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                placeholder="Bạn tên gì?"
            >
            @if ($showErrors)
                @error('guest_name')
                    <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                @enderror
            @endif
        </div>
    </template>
</div>
