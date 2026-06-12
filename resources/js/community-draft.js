/**
 * @param {string} html
 */
export function isBodyEmpty(html) {
    const text = (html ?? '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();

    return text === '';
}

/**
 * @param {{ title?: string, excerpt?: string, body?: string }} data
 */
export function isDraftEmpty(data) {
    const title = (data.title ?? '').trim();
    const excerpt = (data.excerpt ?? '').trim();
    const body = data.body ?? '';

    return title === '' && excerpt === '' && isBodyEmpty(body);
}

/**
 * @param {string} url
 * @param {string} method
 * @param {{ title: string, excerpt: string, body: string }} data
 * @param {string} csrfToken
 */
export async function saveDraftToServer(url, method, data, csrfToken) {
    const response = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(data),
    });

    if (! response.ok) {
        const payload = await response.json().catch(() => ({}));
        throw new Error(payload.message ?? 'Không thể lưu nháp.');
    }

    return response.json();
}

/**
 * @param {string} url
 * @param {string} csrfToken
 */
export async function destroyDraftOnServer(url, csrfToken) {
    const response = await fetch(url, {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    });

    if (! response.ok) {
        throw new Error('Không thể xóa bản nháp.');
    }

    return response.json();
}
