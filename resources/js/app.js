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
