import Quill from 'quill';
import 'quill/dist/quill.snow.css';

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

    form?.addEventListener('submit', () => {
        if (bodyInput) {
            bodyInput.value = quill.root.innerHTML;
        }
    });
});
