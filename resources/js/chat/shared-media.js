import PLAY_SVG from '../icons/play.js';
import DOC_SVG from '../icons/doc.js';
import DOWNLOAD_SVG from '../icons/download.js';

const sharedByChat = {};
let activeUsername = null;
let currentViewKind = null;

const MEDIA_LIMIT = 5;
const FILES_LIMIT = 15;

export function resetSharedMedia(username) {
    activeUsername = username;
    sharedByChat[username] = { media: [], files: [] };
    closeSharedView();
    renderSharedMedia();
}

export function addSharedMedia(msg, username) {
    if (!msg || !msg.file || !['image', 'video', 'document'].includes(msg.type)) return;
    const list = sharedByChat[username];
    if (!list) return;
    const isDup = function (bucket) {
        return bucket.some(function (m) {
            return (msg.id && m.id === msg.id) || (m.file && m.file.url === msg.file.url);
        });
    };
    if (msg.type !== 'document' && !isDup(list.media)) list.media.unshift(msg);
    if (!isDup(list.files)) list.files.unshift(msg);
    renderSharedMedia();
}

export function renderSharedMedia() {
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

    if (currentViewKind) renderView(currentViewKind);
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
        ? `<video src="${escapeHtml(m.file.url)}" preload="metadata" muted playsinline loading="lazy" class="w-full h-full object-cover"></video>`
        : `<img src="${escapeHtml(m.file.preview_url || m.file.url)}" alt="${escapeHtml(m.file.name)}" loading="lazy" decoding="async" class="w-full h-full object-cover">`;
    return `<div data-media-url="${escapeHtml(m.file.url)}" data-media-type="${m.type}" class="relative aspect-square rounded-lg overflow-hidden bg-white/5 cursor-pointer hover:opacity-80 transition-opacity">${inner}${isVideo ? '<span class="absolute inset-0 flex items-center justify-center bg-black/20">' + PLAY_SVG + '</span>' : ''}</div>`;
}

function fileItemHtml(m) {
    const thumb = m.type === 'image'
        ? `<img src="${escapeHtml(m.file.preview_url || m.file.url)}" alt="${escapeHtml(m.file.name)}" loading="lazy" decoding="async" class="w-6 h-6 rounded object-cover shrink-0">`
        : m.type === 'video'
            ? `<video src="${escapeHtml(m.file.url)}" preload="metadata" muted playsinline loading="lazy" class="w-6 h-6 rounded object-cover shrink-0"></video>`
            : `<div class="w-6 h-6 rounded bg-white/10 flex items-center justify-center text-white/50 shrink-0">${DOC_SVG}</div>`;
    return `<div data-files-open="${escapeHtml(m.file.url)}" data-files-type="${m.type}" class="flex items-center gap-2 rounded-lg bg-white/5 hover:bg-white/10 transition-colors px-2 py-1.5 cursor-pointer">
        ${thumb}
        <span class="text-[10px] text-white/70 truncate flex-1 min-w-0">${escapeHtml(m.file.name)}</span>
        <a href="${escapeHtml(m.file.url)}" download="${escapeHtml(m.file.name)}" onclick="event.stopPropagation()" class="shrink-0 text-white/35 hover:text-white transition-colors cursor-pointer" title="Download">${DOWNLOAD_SVG}</a>
    </div>`;
}

function renderView(kind) {
    const title = document.getElementById('rightbar-view-title');
    const container = document.getElementById('rightbar-view-list');
    if (!title || !container) return;
    const list = sharedByChat[activeUsername] || { media: [], files: [] };
    if (kind === 'media') {
        title.textContent = 'Shared Media';
        container.innerHTML = '<div class="grid grid-cols-3 gap-1.5">' + list.media.map(mediaItemHtml).join('') + '</div>';
    } else {
        title.textContent = 'Shared Files';
        container.innerHTML = '<div class="space-y-1.5">' + list.files.map(fileItemHtml).join('') + '</div>';
    }
}

window.openSharedView = function (kind) {
    const view = document.getElementById('rightbar-view');
    if (!view) return;
    currentViewKind = kind;
    renderView(kind);
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
    currentViewKind = null;
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
