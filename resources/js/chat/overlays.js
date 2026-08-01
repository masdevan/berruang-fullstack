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

let addUserPending = false;
let checkTimer = null;
let addUserErrorTimer = null;

function showAddUserError(message) {
    const error = document.getElementById('add-user-error');
    error.textContent = message;
    error.classList.remove('hidden');
    clearTimeout(addUserErrorTimer);
    addUserErrorTimer = setTimeout(function () {
        error.classList.add('hidden');
    }, 3000);
}

function setTopLoader(active) {
    const loader = document.getElementById('top-loader');
    const bar = document.getElementById('top-loader-bar');
    if (!loader || !bar) return;

    if (active) {
        loader.style.display = '';
        bar.style.width = '30%';
        requestAnimationFrame(function () { bar.style.width = '90%'; });
    } else {
        bar.style.width = '100%';
        setTimeout(function () {
            loader.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 250, easing: 'ease-out' }).onfinish = function () {
                loader.style.display = 'none';
            };
        }, 200);
    }
}

const STATUS_SPINNER = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
const STATUS_CHECK = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/></svg>';
const STATUS_X = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

function setAddUserStatus(html, className) {
    const status = document.getElementById('add-user-status');
    status.innerHTML = html;
    status.className = 'absolute right-2.5 top-1/2 -translate-y-1/2 ' + className;
}

function checkUsernameLive() {
    const input = document.getElementById('add-user-input');
    const status = document.getElementById('add-user-status');
    const username = input.value.trim();
    clearTimeout(checkTimer);

    if (!username) {
        status.classList.add('hidden');
        return;
    }

    setAddUserStatus(STATUS_SPINNER, 'text-white/25');
    status.classList.remove('hidden');

    checkTimer = setTimeout(function () {
        fetch('/check-username/' + encodeURIComponent(username))
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (document.getElementById('add-user-input').value.trim() !== username) return;
                setAddUserStatus(data.taken ? STATUS_CHECK : STATUS_X, data.taken ? 'text-green-400' : 'text-red-400');
            })
            .catch(function () { status.classList.add('hidden'); });
    }, 300);
}

window.submitAddUser = function () {
    const input = document.getElementById('add-user-input');
    const username = input.value.trim();
    if (!username || addUserPending) return;
    addUserPending = true;
    setTopLoader(true);

    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch('/contacts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify({ username: username })
    })
        .then(function (response) {
            return response.json().then(function (data) { return { ok: response.ok, data: data }; });
        })
        .then(function ({ ok, data }) {
            setTopLoader(false);
            if (!ok) {
                showAddUserError(data.message || 'Failed to add user.');
                addUserPending = false;
                return;
            }
            const list = document.querySelector('#tab-pane-chat .flex-1.overflow-y-auto');
            list.insertAdjacentHTML('afterbegin', data.html);
            const added = list.firstElementChild;
            if (added) {
                added.animate(
                    [{ opacity: 0, transform: 'translateY(-8px)' }, { opacity: 1, transform: 'translateY(0)' }],
                    { duration: 250, easing: 'ease-out' }
                );
                if (added.dataset.name) added.click();
            }
            document.getElementById('add-user-error').classList.add('hidden');
            input.value = '';
            addUserPending = false;
            closeModal('add-user-modal');
        })
        .catch(function () {
            setTopLoader(false);
            showAddUserError('Failed to add user.');
            addUserPending = false;
        });
};

document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const modal = document.getElementById('add-user-modal');
    if (!modal || modal.classList.contains('hidden')) return;
    if (document.activeElement === document.getElementById('add-user-input')) {
        submitAddUser();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('add-user-input');
    if (input) input.addEventListener('input', checkUsernameLive);
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
