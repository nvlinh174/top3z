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
