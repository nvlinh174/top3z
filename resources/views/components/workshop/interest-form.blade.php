@props([
    'workshop',
    'hasInterest' => false,
])

@php
    /** @var \App\Models\Article $workshop */
    $interestCount = $workshop->interests_count ?? $workshop->interests()->count();
@endphp

<div>
    @if ($hasInterest)
        <x-ui.button variant="secondary" type="button" class="w-full" disabled>
            Bạn đã quan tâm ✓
        </x-ui.button>
    @else
        <form method="POST" action="{{ route('workshops.interest.store', $workshop) }}">
            @csrf

            <div class="hidden" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <x-ui.button variant="primary" type="submit" class="w-full">
                Tôi sẽ tham gia
            </x-ui.button>
        </form>
    @endif

    @if ($interestCount === 0)
        <p class="mt-5 text-center text-sm text-content-muted">
            Hãy là người đầu tiên quan tâm buổi này
        </p>
    @elseif ($interestCount === 1)
        <p class="mt-5 text-center text-sm text-content-muted">
            @if ($hasInterest)
                Cảm ơn bạn — mời thêm bạn bè quan tâm nhé
            @else
                Đã có người quan tâm buổi này
            @endif
        </p>
    @else
        <p class="mt-5 text-center text-sm text-content-muted">
            <span class="font-mono text-brand-400">{{ number_format($interestCount) }}</span>
            người quan tâm
        </p>
    @endif

    <p class="mt-3 text-center text-xs leading-relaxed text-content-muted">
        @auth
            Một cú nhấp — quan tâm được lưu vào tài khoản của bạn.
        @else
            Một cú nhấp — không cần tài khoản, không phải đăng ký giữ chỗ.
        @endauth
    </p>
</div>
