import './bootstrap';
import './community-editor';
import { destroyDraftOnServer, isDraftEmpty, saveDraftToServer } from './community-draft';
import { getCommunityEditor } from './community-editor';
import Alpine from 'alpinejs';

const GUEST_NAME_STORAGE_KEY = 'top3z_guest_name';
const DRAFT_SAVE_DELAY_MS = 3000;

Alpine.data('communityPostForm', (config = {}) => ({
    existingThumbnail: config.existingThumbnail ?? null,
    thumbnailPreview: null,
    existingGallery: config.existingGallery ?? [],
    galleryPreviews: [],
    draftEnabled: config.draftEnabled ?? false,
    draftCreateUrl: config.draftCreateUrl ?? null,
    draftAutosaveUrl: config.draftAutosaveUrl ?? null,
    draftDestroyUrl: config.draftDestroyUrl ?? null,
    latestDraftEditUrl: config.latestDraftEditUrl ?? null,
    draftStatus: 'idle',
    draftSavedAt: null,
    draftTimer: null,
    hasStoredDraft: Boolean(config.draftAutosaveUrl),

    init() {
        if (! this.draftEnabled) {
            return;
        }

        const attachEditor = (api) => {
            api.onChange(() => this.scheduleDraftSave());
            this.$nextTick(() => this.maybeResumeLatestDraft(api));
        };

        const existingEditor = getCommunityEditor();

        if (existingEditor) {
            attachEditor(existingEditor);
        } else {
            window.addEventListener('community-editor:ready', (event) => {
                attachEditor(event.detail);
            }, { once: true });
        }

        this.$el.querySelector('[name="title"]')?.addEventListener('input', () => this.scheduleDraftSave());
        this.$el.querySelector('[name="excerpt"]')?.addEventListener('input', () => this.scheduleDraftSave());
    },

    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    draftSavedLabel() {
        if (! this.draftSavedAt) {
            return '';
        }

        return this.draftSavedAt.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit',
        });
    },

    scheduleDraftSave() {
        if (! this.draftEnabled) {
            return;
        }

        clearTimeout(this.draftTimer);
        this.draftStatus = 'idle';
        this.draftTimer = setTimeout(() => this.persistDraft(), DRAFT_SAVE_DELAY_MS);
    },

    collectDraftData(api = getCommunityEditor()) {
        return {
            title: this.$el.querySelector('[name="title"]')?.value ?? '',
            excerpt: this.$el.querySelector('[name="excerpt"]')?.value ?? '',
            body: api?.getHtml() ?? this.$el.querySelector('#body-input')?.value ?? '',
        };
    },

    async persistDraft() {
        const data = this.collectDraftData();

        if (isDraftEmpty(data)) {
            return;
        }

        this.draftStatus = 'saving';

        try {
            const payload = this.draftAutosaveUrl
                ? await saveDraftToServer(this.draftAutosaveUrl, 'PATCH', data, this.csrfToken())
                : await saveDraftToServer(this.draftCreateUrl, 'POST', data, this.csrfToken());

            this.applyDraftServerState(payload);
            this.draftStatus = 'saved';
            this.draftSavedAt = payload.saved_at ? new Date(payload.saved_at) : new Date();
            this.hasStoredDraft = true;
        } catch {
            this.draftStatus = 'idle';
        }
    },

    applyDraftServerState(payload) {
        this.draftAutosaveUrl = payload.autosave_url ?? this.draftAutosaveUrl;
        this.draftDestroyUrl = payload.destroy_url ?? this.draftDestroyUrl;

        if (payload.edit_url) {
            this.$el.action = payload.edit_url;

            let methodInput = this.$el.querySelector('[name="_method"]');

            if (! methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                this.$el.appendChild(methodInput);
            }

            methodInput.value = 'PATCH';
        }
    },

    isFormEmpty(api = getCommunityEditor()) {
        return isDraftEmpty(this.collectDraftData(api));
    },

    maybeResumeLatestDraft(api) {
        if (! this.latestDraftEditUrl || this.draftAutosaveUrl || ! this.isFormEmpty(api)) {
            return;
        }

        if (! window.confirm('Bạn có bản nháp chưa gửi trên tài khoản. Tiếp tục soạn bản nháp đó?')) {
            return;
        }

        window.location.href = this.latestDraftEditUrl;
    },

    async discardDraft() {
        if (! window.confirm('Xóa bản nháp đã lưu?')) {
            return;
        }

        if (this.draftDestroyUrl) {
            try {
                await destroyDraftOnServer(this.draftDestroyUrl, this.csrfToken());
            } catch {
                return;
            }
        }

        window.location.href = this.$el.querySelector('[data-draft-create-url]')?.dataset.draftCreateUrl
            ?? '/community/create';
    },

    previewThumbnail(event) {
        const file = event.target.files?.[0];

        if (! file) {
            return;
        }

        if (this.thumbnailPreview) {
            URL.revokeObjectURL(this.thumbnailPreview);
        }

        this.thumbnailPreview = URL.createObjectURL(file);
    },

    previewGallery(event) {
        this.clearGalleryPreviews();
        this.galleryPreviews = Array.from(event.target.files ?? []).map((file) => URL.createObjectURL(file));
    },

    clearGalleryPreviews() {
        this.galleryPreviews.forEach((url) => URL.revokeObjectURL(url));
        this.galleryPreviews = [];
    },
}));

Alpine.data('commentReaction', (config = {}) => ({
    toggleUrl: config.toggleUrl ?? '',
    loginUrl: config.loginUrl ?? '/login',
    allowGuest: config.allowGuest ?? false,
    authenticated: config.authenticated ?? false,
    liked: config.liked ?? false,
    count: config.count ?? 0,
    loading: false,

    async toggle() {
        if (! this.allowGuest && ! this.authenticated) {
            window.location.href = this.loginUrl;

            return;
        }

        if (this.loading) {
            return;
        }

        this.loading = true;

        try {
            const response = await fetch(this.toggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
            });

            if (response.status === 401) {
                window.location.href = this.loginUrl;

                return;
            }

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            this.liked = data.active;
            this.count = data.count;
        } finally {
            this.loading = false;
        }
    },
}));

Alpine.data('communityReactions', (config = {}) => ({
    toggleUrl: config.toggleUrl ?? '',
    likesCount: config.likesCount ?? 0,
    favoritesCount: config.favoritesCount ?? 0,
    liked: config.liked ?? false,
    favorited: config.favorited ?? false,
    loading: null,

    async toggle(type) {
        if (this.loading !== null) {
            return;
        }

        this.loading = type;

        try {
            const response = await fetch(this.toggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ type }),
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            this.likesCount = data.counts.like;
            this.favoritesCount = data.counts.favorite;

            if (data.type === 'like') {
                this.liked = data.active;
            }

            if (data.type === 'favorite') {
                this.favorited = data.active;
            }
        } finally {
            this.loading = null;
        }
    },
}));

Alpine.data('notificationBell', (config = {}) => ({
    recentUrl: config.recentUrl ?? '',
    unreadCountUrl: config.unreadCountUrl ?? '',
    readAllUrl: config.readAllUrl ?? '',
    readUrlTemplate: config.readUrlTemplate ?? '',
    indexUrl: config.indexUrl ?? '',
    open: false,
    count: 0,
    items: [],
    loading: false,
    pollTimer: null,

    init() {
        this.refreshCount();
        this.pollTimer = setInterval(() => this.refreshCount(), 60_000);
    },

    destroy() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
        }
    },

    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    async refreshCount() {
        try {
            const response = await fetch(this.unreadCountUrl, {
                headers: { Accept: 'application/json' },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            this.count = data.count ?? 0;
        } catch {
            // ignore network errors for background poll
        }
    },

    async toggle() {
        this.open = ! this.open;

        if (this.open) {
            await this.loadRecent();
        }
    },

    async loadRecent() {
        this.loading = true;

        try {
            const response = await fetch(this.recentUrl, {
                headers: { Accept: 'application/json' },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            this.count = data.count ?? 0;
            this.items = data.notifications ?? [];
        } finally {
            this.loading = false;
        }
    },

    readUrl(id) {
        return this.readUrlTemplate.replace('__ID__', id);
    },

    async openItem(item) {
        try {
            const response = await fetch(this.readUrl(item.id), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken(),
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            if (data.url) {
                window.location.href = data.url;
            }
        } catch {
            // ignore
        }
    },

    async markAllRead() {
        if (this.count === 0 || this.loading) {
            return;
        }

        this.loading = true;

        try {
            const response = await fetch(this.readAllUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken(),
                },
            });

            if (! response.ok) {
                return;
            }

            this.count = 0;
            this.items = this.items.map((item) => ({ ...item, read_at: new Date().toISOString() }));
        } finally {
            this.loading = false;
        }
    },
}));

Alpine.data('homeSlider', (config = {}) => ({
    active: 0,
    count: config.count ?? 0,
    timer: null,
    intervalMs: 5000,

    start() {
        if (this.count <= 1) {
            return;
        }

        this.timer = setInterval(() => this.next(), this.intervalMs);
    },

    destroy() {
        if (this.timer) {
            clearInterval(this.timer);
        }
    },

    pause() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    },

    resume() {
        if (this.count <= 1 || this.timer) {
            return;
        }

        this.start();
    },

    next() {
        this.active = (this.active + 1) % this.count;
    },

    goTo(index) {
        this.active = index;
    },
}));

Alpine.data('guestNameForm', () => ({
    storedName: '',
    draftName: '',
    editingName: false,
    ready: false,

    init() {
        const serverValue = this.$el.querySelector('[data-server-guest-name]')?.value?.trim() ?? '';

        if (serverValue) {
            this.draftName = serverValue;
            this.editingName = true;
            this.ready = true;

            return;
        }

        const stored = localStorage.getItem(GUEST_NAME_STORAGE_KEY)?.trim();

        if (stored) {
            this.storedName = stored;
            this.editingName = false;
        } else {
            this.editingName = true;
        }

        this.ready = true;
    },

    startEditing() {
        this.draftName = this.storedName;
        this.editingName = true;

        this.$nextTick(() => {
            this.$el.querySelector('[data-guest-name]')?.focus();
        });
    },

    remember() {
        const name = this.editingName ? this.draftName.trim() : this.storedName.trim();

        if (name) {
            localStorage.setItem(GUEST_NAME_STORAGE_KEY, name);
            this.storedName = name;
            this.editingName = false;

            return;
        }

        if (this.editingName) {
            localStorage.removeItem(GUEST_NAME_STORAGE_KEY);
            this.storedName = '';
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();
