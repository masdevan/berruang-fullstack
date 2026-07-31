const sectionTips = {};

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
    modal.classList.add('flex');
    if (wasHidden) {
        content.animate(
            [{ opacity: 0, transform: 'scale(0.96)' }, { opacity: 1, transform: 'scale(1)' }],
            { duration: 180, easing: 'ease-out' }
        );
    }
};

window.closeMediaModal = function () {
    const modal = document.getElementById('media-modal');
    if (!modal || modal.classList.contains('hidden')) return;

    const content = document.getElementById('media-modal-content');
    const video = document.getElementById('media-modal-video');

    content.animate(
        [{ opacity: 1, transform: 'scale(1)' }, { opacity: 0, transform: 'scale(0.96)' }],
        { duration: 150, easing: 'ease-in' }
    ).addEventListener('finish', () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        video.pause();
        video.src = '';
    });
};

window.openOverlay = function (id) {
    const overlay = document.getElementById(id);

    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
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
    ).addEventListener('finish', () => {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    });
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

window.toggleAttachMenu = function (event) {
    if (event) event.stopPropagation();
    document.getElementById('attach-menu').classList.toggle('hidden');
};

window.openModal = function (id) {
    const modal = document.getElementById(id);
    if (!modal || !modal.classList.contains('hidden')) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.animate(
        [{ opacity: 0 }, { opacity: 1 }],
        { duration: 150, easing: 'ease-out' }
    );

    const input = modal.querySelector('input[data-autofocus]');
    if (input) {
        setTimeout(function () { input.focus(); }, 100);
    }
};

window.closeModal = function (id) {
    const modal = document.getElementById(id);
    if (!modal || modal.classList.contains('hidden')) return;

    modal.animate(
        [{ opacity: 1 }, { opacity: 0 }],
        { duration: 120, easing: 'ease-in' }
    ).addEventListener('finish', function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
};

window.submitAddUser = function () {
    const input = document.getElementById('add-user-input');
    if (!input.value.trim()) return;

    input.value = '';
    closeModal('add-user-modal');
};

document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const modal = document.getElementById('add-user-modal');
    if (!modal || modal.classList.contains('hidden')) return;
    if (document.activeElement === document.getElementById('add-user-input')) {
        submitAddUser();
    }
});

function getSectionTip(rootId) {
    if (!sectionTips[rootId]) {
        const tip = document.createElement('div');
        tip.className = 'absolute z-50 w-48 bg-[#1A1A1A] border border-white/10 rounded-lg p-2.5 shadow-lg pointer-events-none text-[10px] font-medium normal-case tracking-normal text-white/60 leading-relaxed hidden';
        document.getElementById(rootId).appendChild(tip);
        sectionTips[rootId] = tip;
    }
    return sectionTips[rootId];
}

export function showSectionInfo(btn) {
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

export function hideSectionInfo() {
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
