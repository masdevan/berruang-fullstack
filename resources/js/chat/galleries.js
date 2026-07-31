import { FILE_POOL, fileItemHtml } from './demo-data.js';

const state = {
    mediaNextId: 290,
    mediaLoaded: 30,
    filesLoaded: 0,
};

export function loadMoreMedia() {
    const grid = document.getElementById('media-gallery-grid');
    const count = document.getElementById('media-count');
    if (!grid || state.mediaNextId > 380) return;

    let html = '';
    for (let i = 0; i < 6 && state.mediaNextId <= 380; i++) {
        const id = state.mediaNextId++;
        state.mediaLoaded++;
        const thumb = `https://picsum.photos/id/${id}/200/200`;
        if (id % 9 === 0) {
            html += `<div class="relative aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://www.w3schools.com/html/mov_bbb.mp4', 'video')">
                <img src="${thumb}" alt="Video" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors duration-200 group-hover:bg-black/40">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-white/80"><circle cx="12" cy="12" r="10" fill="rgba(0,0,0,0.45)"/><path d="M10 8l6 4-6 4V8z"/></svg>
                </div>
            </div>`;
        } else {
            html += `<div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/${id}/800/800')">
                <img src="${thumb}" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
            </div>`;
        }
    }
    grid.insertAdjacentHTML('beforeend', html);
    count.textContent = state.mediaLoaded + ' files';
}

export function loadMoreFiles() {
    const list = document.getElementById('files-gallery-list');
    const sentinel = document.getElementById('files-gallery-sentinel');
    const count = document.getElementById('files-count');
    if (!list || state.filesLoaded >= FILE_POOL.length * 4) return;

    let html = '';
    for (let i = 0; i < 6 && state.filesLoaded < FILE_POOL.length * 4; i++) {
        const [icon, name, size] = FILE_POOL[state.filesLoaded % FILE_POOL.length];
        const round = Math.floor(state.filesLoaded / FILE_POOL.length);
        const finalName = round === 0 ? name : name.replace(/(\.[^.]+)$/, `-${round}$1`);
        html += fileItemHtml(icon, finalName, size);
        state.filesLoaded++;
    }
    sentinel.insertAdjacentHTML('beforebegin', html);
    count.textContent = (state.filesLoaded + 26) + ' files';
}

export function watchLoadMore(sentinelId, loader) {
    const sentinel = document.getElementById(sentinelId);
    if (!sentinel) return;
    new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) loader();
    }, { root: sentinel.parentElement }).observe(sentinel);
}
