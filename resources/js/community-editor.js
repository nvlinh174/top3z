import Quill from 'quill';
import 'quill/dist/quill.snow.css';

/**
 * @typedef {{
 *   getHtml: () => string,
 *   setHtml: (html: string) => void,
 *   onChange: (callback: () => void) => void,
 * }} CommunityEditorApi
 */

/** @type {CommunityEditorApi | null} */
let editorApi = null;

/**
 * @returns {CommunityEditorApi | null}
 */
export function getCommunityEditor() {
    return editorApi;
}

/**
 * @param {CommunityEditorApi} api
 */
function publishEditorReady(api) {
    editorApi = api;
    window.__communityEditor = api;
    window.dispatchEvent(new CustomEvent('community-editor:ready', { detail: api }));
}

document.addEventListener('DOMContentLoaded', () => {
    const editorEl = document.getElementById('community-editor');

    if (! editorEl) {
        return;
    }

    const bodyInput = document.getElementById('body-input');
    const form = editorEl.closest('form');

    const quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: 'Viết nội dung bài viết của bạn…',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'link'],
                ['clean'],
            ],
        },
    });

    const initialHtml = bodyInput?.value?.trim() ?? '';

    if (initialHtml !== '') {
        quill.clipboard.dangerouslyPasteHTML(initialHtml);
    }

    /** @type {CommunityEditorApi} */
    const api = {
        getHtml: () => quill.root.innerHTML,
        setHtml: (html) => {
            quill.setContents([]);
            quill.clipboard.dangerouslyPasteHTML(html ?? '');
            if (bodyInput) {
                bodyInput.value = quill.root.innerHTML;
            }
        },
        onChange: (callback) => {
            quill.on('text-change', callback);
        },
    };

    publishEditorReady(api);

    form?.addEventListener('submit', () => {
        if (bodyInput) {
            bodyInput.value = quill.root.innerHTML;
        }
    });
});
