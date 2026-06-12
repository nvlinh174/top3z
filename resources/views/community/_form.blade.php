@props([
    'post' => null,
    'bodyHtml' => '',
    'action',
    'method' => 'POST',
    'enableDraft' => false,
    'draftAutosaveUrl' => null,
    'draftDestroyUrl' => null,
    'latestDraftEditUrl' => null,
])

@php
    use App\Support\CommunityPostDraft;

    $isEdit = $post !== null;
    $isDraft = $post?->isDraftCommunityPost() ?? false;
    $draftEnabled = $enableDraft || $isDraft;
    $initialBody = old('body', $bodyHtml);
    $existingThumbnail = $isEdit ? $post->getThumbnailUrl() : null;
    $existingGallery = $isEdit
        ? $post->getMedia('gallery')->map(fn ($media) => $media->getUrl('large'))->values()->all()
        : [];
    $titleValue = old('title');

    if ($titleValue === null && $post !== null) {
        $titleValue = $post->title === CommunityPostDraft::PLACEHOLDER_TITLE ? '' : $post->title;
    }

    $resolvedAutosaveUrl = $draftAutosaveUrl
        ?? ($isDraft ? route('community.drafts.autosave', $post) : null);
    $resolvedDestroyUrl = $draftDestroyUrl
        ?? ($isDraft ? route('community.drafts.destroy', $post) : null);
@endphp

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    data-community-post-form
    @if ($draftEnabled)
        data-draft-enabled
        data-draft-create-url="{{ route('community.drafts.store') }}"
        @if ($resolvedAutosaveUrl)
            data-draft-autosave-url="{{ $resolvedAutosaveUrl }}"
        @endif
    @endif
    x-data="communityPostForm({
        existingThumbnail: @js($existingThumbnail),
        existingGallery: @js($existingGallery),
        draftEnabled: @js($draftEnabled),
        draftCreateUrl: @js(route('community.drafts.store')),
        draftAutosaveUrl: @js($resolvedAutosaveUrl),
        draftDestroyUrl: @js($resolvedDestroyUrl),
        latestDraftEditUrl: @js($latestDraftEditUrl),
    })"
    class="space-y-6"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_22rem] 2xl:grid-cols-[minmax(0,1fr)_24rem]">
        {{-- Cột chính: tiêu đề + editor --}}
        <div class="min-w-0 space-y-5">
            <x-ui.form-field
                label="Tiêu đề"
                name="title"
                type="text"
                :value="$titleValue"
                required
                autofocus
                placeholder="Nhập tiêu đề bài viết"
            />

            <div>
                <label class="mb-2 block text-sm font-medium text-content-primary">Nội dung</label>
                <div class="community-editor">
                    <div id="community-editor"></div>
                </div>
                <input type="hidden" name="body" id="body-input" value="{{ $initialBody }}">
                @error('body')
                    <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Sidebar phải --}}
        <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
            <x-ui.card class="space-y-4 !p-5">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-content-muted">Xuất bản</h2>
                <p class="text-sm text-content-muted">
                    Danh mục: <span class="font-medium text-content-primary">Chia sẻ trải nghiệm</span>
                </p>
                <p class="text-xs text-content-muted">
                    Bài sẽ được team duyệt trước khi hiển thị trên trang Cộng đồng.
                </p>
                @if ($draftEnabled)
                    <div class="rounded-[var(--radius-button)] border border-zinc-800/80 bg-surface-base/60 px-3 py-2.5 text-xs text-content-muted">
                        <p>Lưu nháp tự động vào tài khoản mỗi vài giây.</p>
                        <p class="mt-1" x-show="draftStatus === 'saving'" x-cloak>Đang lưu nháp…</p>
                        <p class="mt-1" x-show="draftStatus === 'saved'" x-cloak>
                            Đã lưu nháp lúc <span x-text="draftSavedLabel()"></span>
                        </p>
                        <button
                            type="button"
                            x-show="hasStoredDraft"
                            x-cloak
                            @click="discardDraft()"
                            class="mt-2 text-brand-400 hover:text-brand-300"
                        >
                            Xóa bản nháp
                        </button>
                    </div>
                @endif
                <div class="flex flex-col gap-2">
                    <x-ui.button type="submit" class="w-full justify-center">
                        @if ($isDraft)
                            Gửi duyệt
                        @elseif ($isEdit)
                            Gửi lại để duyệt
                        @else
                            Gửi duyệt
                        @endif
                    </x-ui.button>
                    <x-ui.button
                        variant="ghost"
                        href="{{ $isDraft ? route('community.my-posts', ['tab' => 'drafts']) : ($isEdit ? route('community.show', $post) : route('community.index')) }}"
                        class="w-full justify-center"
                    >
                        Huỷ
                    </x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card class="space-y-4 !p-5">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-content-muted">Tóm tắt</h2>
                <div>
                    <label for="excerpt" class="sr-only">Tóm tắt</label>
                    <textarea
                        name="excerpt"
                        id="excerpt"
                        rows="3"
                        maxlength="500"
                        class="w-full rounded-[var(--radius-button)] border border-zinc-700 bg-surface-base px-3 py-2.5 text-sm text-content-primary placeholder:text-content-muted focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                        placeholder="Một dòng giới thiệu hiển thị trên thẻ bài viết"
                    >{{ old('excerpt', $post?->excerpt) }}</textarea>
                    @error('excerpt')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </x-ui.card>

            <x-ui.card class="space-y-4 !p-5">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-content-muted">Ảnh đại diện</h2>

                <template x-if="thumbnailPreview || existingThumbnail">
                    <img
                        :src="thumbnailPreview ?? existingThumbnail"
                        alt="Xem trước ảnh đại diện"
                        class="aspect-[16/9] w-full rounded-[var(--radius-button)] border border-zinc-800 object-cover"
                    >
                </template>

                <div>
                    <input
                        type="file"
                        name="thumbnail"
                        id="thumbnail"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        @change="previewThumbnail"
                        class="w-full text-xs text-content-muted file:me-3 file:rounded-[var(--radius-button)] file:border-0 file:bg-brand-500 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-zinc-950 hover:file:bg-brand-600"
                    >
                    <p class="mt-2 text-xs text-content-muted">JPG, PNG, WebP — tối đa 5MB</p>
                    @error('thumbnail')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </x-ui.card>

            <x-ui.card class="space-y-4 !p-5">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-content-muted">Thư viện ảnh</h2>

                <template x-if="existingGallery.length > 0">
                    <div>
                        <p class="mb-2 text-xs text-content-muted">Ảnh hiện có</p>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="(url, index) in existingGallery" :key="'existing-' + index">
                                <img :src="url" alt="" class="aspect-square rounded-[var(--radius-button)] border border-zinc-800 object-cover">
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="galleryPreviews.length > 0">
                    <div>
                        <p class="mb-2 text-xs text-content-muted">Ảnh mới chọn</p>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="(url, index) in galleryPreviews" :key="'new-' + index">
                                <img :src="url" alt="" class="aspect-square rounded-[var(--radius-button)] border border-brand-500/40 object-cover">
                            </template>
                        </div>
                    </div>
                </template>

                <div>
                    <input
                        type="file"
                        name="gallery[]"
                        id="gallery"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        multiple
                        @change="previewGallery"
                        class="w-full text-xs text-content-muted file:me-3 file:rounded-[var(--radius-button)] file:border-0 file:bg-surface-raised file:px-3 file:py-2 file:text-xs file:font-semibold file:text-content-primary hover:file:bg-surface-overlay"
                    >
                    <p class="mt-2 text-xs text-content-muted">Chọn nhiều ảnh — tối đa 10 file</p>
                    @error('gallery')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    @error('gallery.*')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </x-ui.card>
        </aside>
    </div>
</form>
