const MOBILE_BREAKPOINT = 768;

const TAB_BASE = 'flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 -mb-px transition-colors';
const TAB_ACTIVE = 'text-white border-[#E091A9]';
const TAB_INACTIVE = 'text-white/40 border-transparent';

window.openMediaModal = function (src, type = 'image') {
    const modal = document.getElementById('media-modal');
    const content = document.getElementById('media-modal-content');
    const image = document.getElementById('media-modal-image');
    const video = document.getElementById('media-modal-video');

    image.classList.toggle('hidden', type !== 'image');
    video.classList.toggle('hidden', type !== 'video');
    if (type === 'image') {
        image.src = src;
    } else {
        video.src = src;
    }

    const wasHidden = modal.classList.contains('hidden');

    modal.classList.remove('hidden');
    if (wasHidden) {
        content.animate(
            [{ opacity: 0, transform: 'scale(0.96)' }, { opacity: 1, transform: 'scale(1)' }],
            { duration: 180, easing: 'ease-out' }
        );
    }
};

window.closeMediaModal = function () {
    const modal = document.getElementById('media-modal');
    const content = document.getElementById('media-modal-content');
    const video = document.getElementById('media-modal-video');

    if (modal.classList.contains('hidden')) return;

    content.animate(
        [{ opacity: 1, transform: 'scale(1)' }, { opacity: 0, transform: 'scale(0.96)' }],
        { duration: 150, easing: 'ease-in' }
    ).addEventListener('finish', () => {
        modal.classList.add('hidden');
        video.pause();
        video.src = '';
    });
};

window.openOverlay = function (id) {
    const overlay = document.getElementById(id);

    overlay.classList.remove('hidden');
    overlay.animate(
        [{ transform: 'translateX(100%)' }, { transform: 'translateX(0)' }],
        { duration: 220, easing: 'ease-out' }
    );
};

window.closeOverlay = function (id) {
    const overlay = document.getElementById(id);

    if (overlay.classList.contains('hidden')) return;

    overlay.animate(
        [{ transform: 'translateX(0)' }, { transform: 'translateX(100%)' }],
        { duration: 180, easing: 'ease-in' }
    ).addEventListener('finish', () => overlay.classList.add('hidden'));
};

window.openMediaGallery = function () {
    openOverlay('media-gallery');
};

window.closeMediaGallery = function () {
    closeOverlay('media-gallery');
};

window.openFilesGallery = function () {
    openOverlay('files-gallery');
};

window.closeFilesGallery = function () {
    closeOverlay('files-gallery');
};

window.switchTab = function (tab) {
    const isChat = tab === 'chat';

    document.getElementById('tab-pane-chat').classList.toggle('hidden', !isChat);
    document.getElementById('tab-pane-workspace').classList.toggle('hidden', isChat);

    document.getElementById('tab-btn-chat').className = TAB_BASE + ' ' + (isChat ? TAB_ACTIVE : TAB_INACTIVE);
    document.getElementById('tab-btn-workspace').className = TAB_BASE + ' ' + (isChat ? TAB_INACTIVE : TAB_ACTIVE);
};

window.toggleLeft = function () {
    if (window.innerWidth < MOBILE_BREAKPOINT) {
        backToConversations();
        return;
    }

    const el = document.getElementById('sidebar-left');
    el.style.transition = 'width 0.2s';
    el.style.width = el.style.width === '0px' ? '320px' : '0px';
    setTimeout(() => el.style.transition = '', 200);
};

window.toggleRight = function () {
    const el = document.getElementById('sidebar-right');

    if (window.innerWidth < MOBILE_BREAKPOINT) {
        el.style.width = '';
        el.classList.toggle('translate-x-full');
        return;
    }

    el.style.transition = 'width 0.2s';
    el.style.width = el.style.width === '0px' ? '288px' : '0px';
    setTimeout(() => el.style.transition = '', 200);
};

window.openConversation = function () {
    if (window.innerWidth >= MOBILE_BREAKPOINT) return;

    const list = document.getElementById('sidebar-left');
    const area = document.getElementById('message-area');

    list.classList.add('hidden');
    list.style.width = '';
    area.classList.remove('hidden');
    area.classList.add('flex');
};

window.backToConversations = function () {
    if (window.innerWidth >= MOBILE_BREAKPOINT) return;

    const list = document.getElementById('sidebar-left');
    const area = document.getElementById('message-area');

    area.classList.add('hidden');
    area.classList.remove('flex');
    list.classList.remove('hidden');
    list.style.width = '';

    document.getElementById('sidebar-right').classList.add('translate-x-full');
};

window.toggleSearch = function () {
    const bar = document.getElementById('search-bar');
    const input = document.getElementById('search-input');
    bar.classList.toggle('hidden');
    if (!bar.classList.contains('hidden')) input.focus();
};

window.searchConversations = function () {
    const input = document.getElementById('search-input');
    input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter' }));
};

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('search-input');
    if (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const val = this.value.toLowerCase().trim();
                document.querySelectorAll('[data-conversation]').forEach(el => {
                    el.style.display = val ? (el.dataset.conversation.includes(val) ? '' : 'none') : '';
                });
            }
        });
    }

    makeResizable('sidebar-left', 'resize-left', 200, 500);
    makeResizable('sidebar-right', 'resize-right', 200, 400);

    let mediaNextId = 290;
    let mediaLoaded = 30;
    let filesLoaded = 0;

    const FILE_ICONS = {
        'file-doc': 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        'file-image': 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z',
        'file-video': 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z',
        'file-archive': 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
    };

    const FILE_POOL = [
        ['file-doc', 'brand-assets-v2.pdf', '5.4 MB'],
        ['file-image', 'og-cover.png', '980 KB'],
        ['file-video', 'feature-overview.mp4', '18.3 MB'],
        ['file-archive', 'source-files.zip', '27.5 MB'],
        ['file-doc', 'sprint-planning.docx', '1.6 MB'],
        ['file-image', 'dark-mode-mockup.png', '1.3 MB'],
        ['file-doc', 'release-notes.pdf', '740 KB'],
        ['file-video', 'tutorial-clip.mp4', '12.8 MB'],
        ['file-archive', 'backup-export.zip', '51.2 MB'],
        ['file-image', 'banner-concept.png', '2.5 MB'],
    ];

    function fileItemHtml(icon, name, size) {
        return `<div class="flex items-center gap-2 p-1.5 rounded hover:bg-white/5 transition-colors cursor-pointer group">
                    <div class="w-7 h-7 rounded bg-white/5 flex items-center justify-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5 text-white/35">
                            <path stroke-linecap="round" stroke-linejoin="round" d="${FILE_ICONS[icon]}"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-medium text-white/65 truncate">${name}</p>
                        <p class="text-[9px] text-white/25 truncate">${size}</p>
                    </div>
                    <button type="button" class="shrink-0 text-white/20 hover:text-white transition-colors cursor-pointer" title="Download">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                    </button>
                </div>`;
    }

    function loadMoreMedia() {
        const grid = document.getElementById('media-gallery-grid');
        const count = document.getElementById('media-count');
        if (!grid || mediaNextId > 380) return;

        let html = '';
        for (let i = 0; i < 6 && mediaNextId <= 380; i++) {
            const id = mediaNextId++;
            mediaLoaded++;
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
        count.textContent = mediaLoaded + ' files';
    }

    function loadMoreFiles() {
        const list = document.getElementById('files-gallery-list');
        const sentinel = document.getElementById('files-gallery-sentinel');
        const count = document.getElementById('files-count');
        if (!list || filesLoaded >= FILE_POOL.length * 4) return;

        let html = '';
        for (let i = 0; i < 6 && filesLoaded < FILE_POOL.length * 4; i++) {
            const [icon, name, size] = FILE_POOL[filesLoaded % FILE_POOL.length];
            const round = Math.floor(filesLoaded / FILE_POOL.length);
            const finalName = round === 0 ? name : name.replace(/(\.[^.]+)$/, `-${round}$1`);
            html += fileItemHtml(icon, finalName, size);
            filesLoaded++;
        }
        sentinel.insertAdjacentHTML('beforebegin', html);
        count.textContent = (filesLoaded + 26) + ' files';
    }

    function watchLoadMore(sentinelId, loader) {
        const sentinel = document.getElementById(sentinelId);
        if (!sentinel) return;
        new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) loader();
        }, { root: sentinel.parentElement }).observe(sentinel);
    }

    watchLoadMore('media-gallery-sentinel', loadMoreMedia);
    watchLoadMore('files-gallery-sentinel', loadMoreFiles);

    document.querySelectorAll('.section-info-btn').forEach(function (btn) {
        btn.addEventListener('mouseenter', function () { showSectionInfo(this); });
        btn.addEventListener('mouseleave', hideSectionInfo);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMediaModal();
    });

    const mobileQuery = window.matchMedia('(max-width: 767px)');
    mobileQuery.addEventListener('change', function (e) {
        if (e.matches) {
            document.getElementById('sidebar-left').style.width = '';
            document.getElementById('sidebar-right').style.width = '';
        }
    });
});

const sectionTips = {};

function getSectionTip(rootId) {
    if (!sectionTips[rootId]) {
        const tip = document.createElement('div');
        tip.className = 'absolute z-50 w-48 bg-[#1A1A1A] border border-white/10 rounded-lg p-2.5 shadow-lg pointer-events-none text-[10px] font-medium normal-case tracking-normal text-white/60 leading-relaxed hidden';
        document.getElementById(rootId).appendChild(tip);
        sectionTips[rootId] = tip;
    }
    return sectionTips[rootId];
}

function showSectionInfo(btn) {
    const rootId = btn.dataset.tipRoot;
    const tip = getSectionTip(rootId);
    const root = document.getElementById(rootId);
    const rootRect = root.getBoundingClientRect();
    const btnRect = btn.getBoundingClientRect();
    const tipWidth = 192;
    const tipHeight = tip.offsetHeight || 80;

    tip.textContent = btn.querySelector('span').textContent;

    let x = btnRect.left - rootRect.left;
    let y = btnRect.bottom - rootRect.top + 6;

    if (y + tipHeight > rootRect.height) {
        y = btnRect.top - rootRect.top - tipHeight - 6;
    }
    if (x + tipWidth > rootRect.width) {
        x = rootRect.width - tipWidth - 8;
    }

    tip.style.top = Math.max(8, y) + 'px';
    tip.style.left = Math.max(8, x) + 'px';
    tip.classList.remove('hidden');
}

function hideSectionInfo() {
    Object.values(sectionTips).forEach(function (tip) { tip.classList.add('hidden'); });
}

window.toggleSectionInfo = function (btn) {
    const rootId = btn.dataset.tipRoot;
    const tip = getSectionTip(rootId);

    if (!tip.classList.contains('hidden') && tip.textContent === btn.querySelector('span').textContent) {
        hideSectionInfo();
    } else {
        showSectionInfo(btn);
    }
};

function makeResizable(id, handleId, minWidth, maxWidth) {
    const el = document.getElementById(id);
    const handle = document.getElementById(handleId);
    if (!el || !handle) return;
    let startX, startWidth;

    handle.addEventListener('mousedown', function (e) {
        startX = e.clientX;
        startWidth = el.offsetWidth;
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });

    function onMove(e) {
        const delta = e.clientX - startX;
        let newWidth = id === 'sidebar-left' ? startWidth + delta : startWidth - delta;
        newWidth = Math.max(minWidth, Math.min(maxWidth, newWidth));
        el.style.width = newWidth + 'px';
    }

    function onUp() {
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', onUp);
    }
}
