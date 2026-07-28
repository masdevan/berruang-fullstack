window.toggleLeft = function () {
    const el = document.getElementById('sidebar-left');
    el.style.transition = 'width 0.2s';
    el.style.width = el.style.width === '0px' ? '320px' : '0px';
    setTimeout(() => el.style.transition = '', 200);
};

window.toggleRight = function () {
    const el = document.getElementById('sidebar-right');
    el.style.transition = 'width 0.2s';
    el.style.width = el.style.width === '0px' ? '288px' : '0px';
    setTimeout(() => el.style.transition = '', 200);
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
});

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
