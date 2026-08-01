function animateIn(el, frames, duration) {
    el.classList.remove('hidden');
    el.classList.add('flex');
    el.animate(frames, { duration: duration, easing: 'ease-out' });
}

function animateOut(el, frames, duration, onFinish) {
    el.animate(frames, { duration: duration, easing: 'ease-in' }).addEventListener('finish', onFinish);
}

window.openMediaModal = function (src, type = 'image') {
    const modal = document.getElementById('media-modal');
    const content = document.getElementById('media-modal-content');
    const image = document.getElementById('media-modal-image');
    const video = document.getElementById('media-modal-video');
    const wasHidden = modal.classList.contains('hidden');

    image.classList.toggle('hidden', type !== 'image');
    video.classList.toggle('hidden', type !== 'video');
    (type === 'image' ? image : video).src = src;

    if (wasHidden) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        content.animate(
            [{ opacity: 0, transform: 'scale(0.96)' }, { opacity: 1, transform: 'scale(1)' }],
            { duration: 180, easing: 'ease-out' }
        );
    }
};

window.closeMediaModal = function () {
    const modal = document.getElementById('media-modal');
    if (!modal || modal.classList.contains('hidden')) return;

    animateOut(document.getElementById('media-modal-content'), [
        { opacity: 1, transform: 'scale(1)' },
        { opacity: 0, transform: 'scale(0.96)' },
    ], 150, function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('media-modal-video').pause();
    });
};

window.toggleAttachMenu = function (event) {
    if (event) event.stopPropagation();
    document.getElementById('attach-menu').classList.toggle('hidden');
};

export function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal || !modal.classList.contains('hidden')) return;

    animateIn(modal, [{ opacity: 0 }, { opacity: 1 }], 150);

    const input = modal.querySelector('input[data-autofocus]');
    if (input) {
        if (!input.value.trim()) {
            const status = modal.querySelector('#add-user-status');
            if (status) status.classList.add('hidden');
        }
        setTimeout(function () { input.focus(); }, 100);
    }
}

window.openModal = openModal;

export function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal || modal.classList.contains('hidden')) return;

    animateOut(modal, [{ opacity: 1 }, { opacity: 0 }], 120, function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
}

window.closeModal = closeModal;

window.openBioModal = function () {
    const text = document.getElementById('rightbar-about-text').textContent.trim();
    if (!text) return;
    document.getElementById('bio-modal-text').textContent = text;
    openModal('bio-modal');
};
