const sharedByChat = {};
let activeUsername = null;

const MEDIA_LIMIT = 5;
const FILES_LIMIT = 15;

const PLAY_SVG = '<svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-white"><path d="M8 5v14l11-7z"/></svg>';
const DOC_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>';

export function resetSharedMedia(username) {
    activeUsername = username;
    sharedByChat[username] = { media: [], files: [] };
    closeSharedView();
    renderSharedMedia();
}

export function addSharedMedia(msg, username) {
    if (!msg || !msg.file || !msg.id || !['image', 'video', 'document'].includes(msg.type)) return;
    const list = sharedByChat[username];
    if (!list) return;
    if (msg.type !== 'document' && !list.media.some(m => m.id === msg.id)) list.media.push(msg);
    if (!list.files.some(m => m.id === msg.id)) list.files.push(msg);
    renderSharedMedia();
}

function renderSharedMedia() {
    const list = sharedByChat[activeUsername];
    const mediaList = document.getElementById('shared-media-list');
    const filesList = document.getElementById('shared-files-list');
    if (!mediaList || !filesList) return;

    const mediaAll = list ? list.media : [];
    const filesAll = list ? list.files : [];

    toggleEmpty('shared-media-empty', mediaAll.length);
    toggleEmpty('shared-files-empty', filesAll.length);

    mediaList.classList.toggle('hidden', mediaAll.length === 0);
    mediaList.classList.toggle('grid', mediaAll.length > 0);
    const media = mediaAll.slice(0, MEDIA_LIMIT);
    const mediaExtra = mediaAll.length - media.length;
    const expandTile = mediaExtra > 0
        ? `<div data-media-expand class="relative aspect-square rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-xs font-medium text-white/60 cursor-pointer hover:bg-white/10 hover:text-white transition-colors">+${mediaExtra}</div>`
        : '';
    mediaList.innerHTML = media.map(mediaItemHtml).join('') + expandTile;

    filesList.classList.toggle('hidden', filesAll.length === 0);
    filesList.innerHTML = filesAll.slice(0, FILES_LIMIT).map(fileItemHtml).join('');

    const mediaViewBtn = document.getElementById('shared-media-viewall');
    const filesViewBtn = document.getElementById('shared-files-viewall');
    if (mediaViewBtn) mediaViewBtn.classList.toggle('hidden', mediaAll.length === 0);
    if (filesViewBtn) filesViewBtn.classList.toggle('hidden', filesAll.length === 0);
}

function toggleEmpty(id, count) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('hidden', count > 0);
    el.classList.toggle('flex', count === 0);
}

function mediaItemHtml(m) {
    const isVideo = m.type === 'video';
    const inner = isVideo
        ? `<video src="${escapeHtml(m.file.url)}" preload="metadata" muted playsinline class="w-full h-full object-cover"></video>`
        : `<img src="${escapeHtml(m.file.url)}" alt="${escapeHtml(m.file.name)}" class="w-full h-full object-cover">`;
    return `<div data-media-url="${escapeHtml(m.file.url)}" data-media-type="${m.type}" class="relative aspect-square rounded-lg overflow-hidden bg-white/5 cursor-pointer hover:opacity-80 transition-opacity">${inner}${isVideo ? '<span class="absolute inset-0 flex items-center justify-center bg-black/20">' + PLAY_SVG + '</span>' : ''}</div>`;
}

function fileItemHtml(m) {
    const thumb = m.type === 'image'
        ? `<img src="${escapeHtml(m.file.url)}" alt="${escapeHtml(m.file.name)}" class="w-6 h-6 rounded object-cover shrink-0">`
        : m.type === 'video'
            ? `<video src="${escapeHtml(m.file.url)}" preload="metadata" muted playsinline class="w-6 h-6 rounded object-cover shrink-0"></video>`
            : `<div class="w-6 h-6 rounded bg-white/10 flex items-center justify-center text-white/50 shrink-0">${DOC_SVG}</div>`;
    return `<div data-files-open="${escapeHtml(m.file.url)}" data-files-type="${m.type}" class="flex items-center gap-2 rounded-lg bg-white/5 hover:bg-white/10 transition-colors px-2 py-1.5 cursor-pointer">
        ${thumb}
        <span class="text-[10px] text-white/70 truncate">${escapeHtml(m.file.name)}</span>
    </div>`;
}

window.openSharedView = function (kind) {
    const view = document.getElementById('rightbar-view');
    const title = document.getElementById('rightbar-view-title');
    const container = document.getElementById('rightbar-view-list');
    if (!view || !title || !container) return;
    const list = sharedByChat[activeUsername] || { media: [], files: [] };
    if (kind === 'media') {
        title.textContent = 'Shared Media';
        container.innerHTML = '<div class="grid grid-cols-3 gap-1.5">' + list.media.map(mediaItemHtml).join('') + '</div>';
    } else {
        title.textContent = 'Shared Files';
        container.innerHTML = '<div class="space-y-1.5">' + list.files.map(fileItemHtml).join('') + '</div>';
    }
    view.classList.remove('hidden');
    view.classList.add('flex');
    view.animate(
        [{ transform: 'translateX(100%)' }, { transform: 'translateX(0)' }],
        { duration: 180, easing: 'ease-out' }
    );
};

window.closeSharedView = function () {
    const view = document.getElementById('rightbar-view');
    if (!view || view.classList.contains('hidden')) return;
    view.animate(
        [{ transform: 'translateX(0)' }, { transform: 'translateX(100%)' }],
        { duration: 150, easing: 'ease-in' }
    ).addEventListener('finish', function () {
        view.classList.add('hidden');
        view.classList.remove('flex');
    });
};

document.addEventListener('click', function (e) {
    if (e.target.closest('[data-media-expand]')) {
        openSharedView('media');
        return;
    }
    const fileEl = e.target.closest('[data-files-open]');
    if (fileEl) {
        if (fileEl.dataset.filesType === 'document') window.open(fileEl.dataset.filesOpen, '_blank');
        else window.openMediaModal(fileEl.dataset.filesOpen, fileEl.dataset.filesType);
        return;
    }
    const mediaEl = e.target.closest('[data-media-url]');
    if (!mediaEl) return;
    window.openMediaModal(mediaEl.dataset.mediaUrl, mediaEl.dataset.mediaType === 'video' ? 'video' : 'image');
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
