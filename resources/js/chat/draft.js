const PREFIX = 'berruang-draft:';

export function getDraft(username) {
    return localStorage.getItem(PREFIX + username) || '';
}

export function saveDraft(username, text) {
    if (text) {
        localStorage.setItem(PREFIX + username, text);
    } else {
        localStorage.removeItem(PREFIX + username);
    }
    applyDraftPreview(username);
}

export function applyDraftPreview(username) {
    if (!username) return;
    const item = document.querySelector('[data-username="' + username + '"]');
    const preview = item && item.querySelector('.conversation-last');
    if (!preview) return;
    const draft = getDraft(username);
    if (preview.dataset.draftOriginal === undefined) preview.dataset.draftOriginal = preview.textContent;
    if (draft) {
        preview.textContent = 'Draft: ' + draft;
        preview.classList.add('text-[#E091A9]/80');
    } else {
        preview.textContent = preview.dataset.draftOriginal;
        delete preview.dataset.draftOriginal;
        preview.classList.remove('text-[#E091A9]/80');
    }
}
