const PREFIX = 'berruang-draft:';
const syncTimers = {};

export function getDraft(username) {
    return localStorage.getItem(PREFIX + username) || '';
}

export function sendDraftSync(username) {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;
    const text = getDraft(username) || (window.getPendingLabel && window.getPendingLabel(username)) || '';
    navigator.sendBeacon(
        '/chat/draft?to=' + encodeURIComponent(username) +
        '&text=' + encodeURIComponent(text) +
        '&_token=' + encodeURIComponent(token.content)
    );
}

export function hasAnyDraft(username) {
    if (getDraft(username)) return true;
    return !!(window.getPendingLabel && window.getPendingLabel(username));
}

export function saveDraft(username, text) {
    if (text) {
        localStorage.setItem(PREFIX + username, text);
    } else {
        localStorage.removeItem(PREFIX + username);
    }
    applyDraftPreview(username);
    clearTimeout(syncTimers[username]);
    syncTimers[username] = setTimeout(function () { sendDraftSync(username); }, 400);
}

export function applyDraftPreview(username) {
    if (!username) return;
    const item = document.querySelector('[data-username="' + username + '"]');
    const preview = item && item.querySelector('.conversation-last');
    if (!preview) return;
    const draft = getDraft(username);
    let label = draft ? 'Draft: ' + draft : '';
    if (!label && window.getPendingLabel) {
        const pending = window.getPendingLabel(username);
        if (pending) label = 'Draft: ' + pending;
    }
    if (preview.dataset.draftOriginal === undefined && preview.textContent.indexOf('Draft: ') !== 0) {
        preview.dataset.draftOriginal = preview.textContent;
    }
    if (label) {
        preview.textContent = label;
        preview.classList.remove('text-white/35');
        preview.classList.add('text-[#E091A9]/80');
    } else {
        preview.textContent = preview.dataset.draftOriginal !== undefined
            ? preview.dataset.draftOriginal
            : (item.dataset.lastMessage || '');
        delete preview.dataset.draftOriginal;
        preview.classList.remove('text-[#E091A9]/80');
        preview.classList.add('text-white/35');
    }
}

export function applyAllDrafts() {
    document.querySelectorAll('[data-username]').forEach(function (item) {
        applyDraftPreview(item.dataset.username);
    });
}
