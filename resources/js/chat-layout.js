import './chat/modal.js';
import './chat/add-user.js';
import './chat/search.js';
import './chat/realtime.js';
import './chat/idle.js';
import { makeResizable, setRightbarVisible } from './chat/sidebar.js';
import { showSectionInfo, hideSectionInfo } from './chat/section-info.js';
import { recalcUnreadTotal } from './chat/unread.js';
import { applyAllDrafts } from './chat/draft.js';

document.addEventListener('DOMContentLoaded', function () {
    recalcUnreadTotal();
    applyAllDrafts();
    requestAnimationFrame(function () {
        document.body.classList.remove('js-loading');
    });
    const input = document.getElementById('search-input');
    if (input) {
        input.addEventListener('input', window.filterLists);
    }

    setRightbarVisible(false);

    makeResizable('sidebar-left', 'resize-left', 200, 500);
    makeResizable('sidebar-right', 'resize-right', 200, 400);

    let contactsPage = 1;
    let contactsLoading = false;
    const contactsSentinel = document.getElementById('contacts-sentinel');
    if (contactsSentinel) {
        new IntersectionObserver(function (entries) {
            if (!entries[0].isIntersecting || contactsLoading) return;
            contactsLoading = true;
            contactsPage++;
            fetch('/contacts?page=' + contactsPage)
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    contactsSentinel.insertAdjacentHTML('beforebegin', data.html);
                    recalcUnreadTotal();
                    applyAllDrafts();
                    if (!data.has_more) contactsSentinel.remove();
                    contactsLoading = false;
                })
                .catch(function () { contactsPage--; contactsLoading = false; });
        }, { root: contactsSentinel.parentElement }).observe(contactsSentinel);
    }

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
