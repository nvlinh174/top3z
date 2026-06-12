import './bootstrap';
import './community-editor';
import Alpine from 'alpinejs';

const GUEST_NAME_STORAGE_KEY = 'top3z_guest_name';

Alpine.data('communityPostForm', (config = {}) => ({
    existingThumbnail: config.existingThumbnail ?? null,
    thumbnailPreview: null,
    existingGallery: config.existingGallery ?? [],
    galleryPreviews: [],

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
    authenticated: config.authenticated ?? false,
    liked: config.liked ?? false,
    count: config.count ?? 0,
    loading: false,

    async toggle() {
        if (! this.authenticated) {
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
