@if (session('success') || session('info') || session('error'))
    <div class="mx-auto max-w-6xl space-y-3 px-4 pt-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="rounded-lg border border-brand-500/30 bg-brand-500/10 px-4 py-3 text-sm text-brand-300" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="rounded-lg border border-zinc-700 bg-surface-raised px-4 py-3 text-sm text-content-muted" role="status">
                {{ session('info') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300" role="alert">
                {{ session('error') }}
            </div>
        @endif
    </div>
@endif
