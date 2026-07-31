import './chat/overlays.js';
import './chat/search.js';
import { loadMoreMedia, loadMoreFiles, watchLoadMore } from './chat/galleries.js';
import { makeResizable, setRightbarVisible } from './chat/sidebar.js';
import { showSectionInfo, hideSectionInfo } from './chat/overlays.js';
import { DEMO_CONVERSATIONS } from './chat/demo-data.js';

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('search-input');
    if (input) {
        input.addEventListener('input', window.filterLists);
    }

    const chatParam = new URLSearchParams(window.location.search).get('chat');
    if (chatParam) {
        const chat = DEMO_CONVERSATIONS[chatParam];
        if (chat) openConversation(chatParam, chat.avatar, chat.online);
    } else {
        setRightbarVisible(false);
    }

    makeResizable('sidebar-left', 'resize-left', 200, 500);
    makeResizable('sidebar-right', 'resize-right', 200, 400);

    watchLoadMore('media-gallery-sentinel', loadMoreMedia);
    watchLoadMore('files-gallery-sentinel', loadMoreFiles);

    document.querySelectorAll('.section-info-btn').forEach(function (btn) {
        btn.addEventListener('mouseenter', function () { showSectionInfo(this); });
        btn.addEventListener('mouseleave', hideSectionInfo);
    });

    document.addEventListener('click', function (e) {
        const menu = document.getElementById('attach-menu');
        if (menu) menu.classList.add('hidden');

        const fabMenu = document.getElementById('fab-menu');
        if (fabMenu && !fabMenu.classList.contains('hidden') && !e.target.closest('#fab-btn, #fab-menu')) {
            fabMenu.classList.add('hidden');
            const fabBtn = document.getElementById('fab-btn');
            if (fabBtn) fabBtn.classList.remove('rotate-45');
        }
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
