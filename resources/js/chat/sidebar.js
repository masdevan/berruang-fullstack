import { MOBILE_BREAKPOINT, TAB_BASE, TAB_ACTIVE, TAB_INACTIVE } from './constants.js';
import { setCurrentChat, currentChatName } from './bubbles.js';
import { openChat } from './messaging.js';
import { clearUnread } from './unread.js';
import { setStatus } from './realtime.js';
import { getDraft, applyDraftPreview } from './draft.js';

window.switchTab = function (tab) {
    const isChat = tab === 'chat';
    const chatPane = document.getElementById('tab-pane-chat');
    const workspacePane = document.getElementById('tab-pane-workspace');

    chatPane.classList.toggle('hidden', !isChat);
    chatPane.classList.toggle('flex', isChat);
    workspacePane.classList.toggle('hidden', isChat);
    workspacePane.classList.toggle('flex', !isChat);

    document.getElementById('tab-btn-chat').className = TAB_BASE + ' ' + (isChat ? TAB_ACTIVE : TAB_INACTIVE);
    document.getElementById('tab-btn-workspace').className = TAB_BASE + ' ' + (isChat ? TAB_INACTIVE : TAB_ACTIVE);

    const input = document.getElementById('search-input');
    if (input) input.placeholder = isChat ? 'Search conversations...' : 'Search workspaces...';

    if (window.filterLists) window.filterLists();
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

window.closeRightbar = function () {
    if (window.innerWidth < MOBILE_BREAKPOINT) {
        const el = document.getElementById('sidebar-right');
        if (el) {
            el.style.width = '';
            el.classList.add('translate-x-full');
        }
        return;
    }
    setRightbarVisible(false);
};

window.toggleRight = function () {
    const el = document.getElementById('sidebar-right');
    if (!el) return;

    showRightbarEmptyState(!rightbarHasChat);

    if (window.innerWidth < MOBILE_BREAKPOINT) {
        el.style.width = '';
        el.classList.toggle('translate-x-full');
        return;
    }

    el.style.transition = 'width 0.2s';
    el.style.width = el.style.width === '0px' ? '288px' : '0px';
    setTimeout(() => el.style.transition = '', 200);
};

let rightbarHasChat = false;

export function setRightbarHasChat(hasChat) {
    rightbarHasChat = !!hasChat;
}

function showRightbarEmptyState(empty) {
    const el = document.getElementById('rightbar-empty');
    if (!el) return;
    el.classList.toggle('hidden', !empty);
    el.classList.toggle('flex', empty);
}

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

window.goBackToConversations = function () {
    if (window.innerWidth >= MOBILE_BREAKPOINT) return;
    backToConversations();
    history.replaceState({}, '', '/messages');
};

window.openConversation = function (name, avatar, status, about, customName, realName, username, hasAvatar) {

    const headerAvatar = document.getElementById('chat-header-avatar');
    if (!headerAvatar) {
        window.location.href = '/messages?chat=' + encodeURIComponent(name);
        return;
    }

    if (window.leaveWorkspaceChat) window.leaveWorkspaceChat();

    const workspace = document.getElementById('chat-workspace');
    const noChat = document.getElementById('no-chat');
    workspace.classList.remove('hidden');
    workspace.classList.add('flex');
    noChat.classList.add('hidden');
    noChat.classList.remove('flex');

    if (window.innerWidth < MOBILE_BREAKPOINT) {
        const list = document.getElementById('sidebar-left');
        const area = document.getElementById('message-area');

        list.classList.add('hidden');
        list.style.width = '';
        area.classList.remove('hidden');
        area.classList.add('flex');

        if (!new URLSearchParams(window.location.search).get('chat')) {
            history.pushState({ chat: username }, '', '?chat=' + encodeURIComponent(username));
        }
    }

    const AVATAR_IMG = hasAvatar
        ? '<img src="' + avatar + '" class="w-full h-full object-cover rounded-full">'
        : avatar;
    headerAvatar.innerHTML = AVATAR_IMG;
    const headerNameEl = document.getElementById('chat-header-name');
    headerNameEl.textContent = customName ? name : '@' + username;
    headerNameEl.title = customName ? '' : 'Save contact';
    headerNameEl.classList.toggle('cursor-pointer', !customName);
    headerNameEl.classList.toggle('hover:text-[#E091A9]', !customName);
    headerNameEl.onclick = customName ? null : function () { window.openSaveContactModal(); };

    const customNameEl = document.getElementById('rightbar-custom-name');
    const realNameEl = document.getElementById('rightbar-real-name');
    const usernameEl = document.getElementById('rightbar-username');
    customNameEl.textContent = name;
    customNameEl.classList.toggle('hidden', !customName);
    document.getElementById('rightbar-real-name-text').textContent = realName;
    realNameEl.classList.toggle('hidden', !realName);
    document.getElementById('rightbar-unsaved-badge').classList.toggle('hidden', !!customName);
    usernameEl.textContent = '@' + username;
    usernameEl.classList.toggle('hidden', !username);
    document.getElementById('rightbar-save-contact').classList.toggle('hidden', !!customName);
    const avatarEl = document.getElementById('rightbar-avatar');
    avatarEl.innerHTML = AVATAR_IMG;
    avatarEl.onclick = hasAvatar ? function () {
        const item = document.querySelector('[data-username="' + username + '"]');
        window.openMediaModal((item && item.dataset.fullAvatar) || avatar);
    } : null;
    avatarEl.title = hasAvatar ? 'View profile photo' : '';
    avatarEl.classList.toggle('cursor-pointer', !!hasAvatar);
    document.getElementById('rightbar-about-text').textContent = about;
    rightbarHasChat = true;
    showRightbarEmptyState(false);
    setRightbarVisible(true);

    const SWITCH_FADE = [{ opacity: 0, transform: 'translateY(-4px)' }, { opacity: 1, transform: 'translateY(0)' }];
    document.getElementById('chat-header-info').animate(SWITCH_FADE, { duration: 150, easing: 'ease-out' });
    const rightbarProfile = document.getElementById('rightbar-profile');
    if (rightbarProfile) {
        rightbarProfile.animate(SWITCH_FADE, { duration: 150, easing: 'ease-out' });
    }

    const container = document.getElementById('messages-container');
    applyDraftPreview(currentChatName);
    setCurrentChat(username);
    setStatus(username, status || 'offline');
    clearUnread(username);
    openChat(username);
    container.innerHTML = '';
    container.scrollTop = container.scrollHeight;
    const inputBar = document.getElementById('chat-input-bar');
    if (inputBar) inputBar.classList.remove('hidden');
    const workspaceTabs = document.getElementById('workspace-tabs');
    if (workspaceTabs) {
        workspaceTabs.classList.add('hidden');
        workspaceTabs.classList.remove('flex');
    }
    const wsPanel = document.getElementById('rightbar-workspace');
    if (wsPanel) {
        wsPanel.classList.add('hidden');
        wsPanel.classList.remove('flex');
    }
    const input = document.getElementById('message-input');
    if (input) {
        input.value = getDraft(username);
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
    }
    applyDraftPreview(username);
    if (window.renderAttachPreview) window.renderAttachPreview();
    container.animate(
        [{ opacity: 0, transform: 'translateY(6px)' }, { opacity: 1, transform: 'translateY(0)' }],
        { duration: 150, easing: 'ease-out' }
    );

    document.querySelectorAll('[data-name]').forEach(function (item) {
        item.classList.remove('bg-white/5');
    });
    const activeItem = document.querySelector('[data-name="' + name + '"]');
    if (activeItem) activeItem.classList.add('bg-white/5');
};

export function setRightbarVisible(visible) {
    const el = document.getElementById('sidebar-right');
    if (!el) return;

    if (window.innerWidth < MOBILE_BREAKPOINT) {
        el.classList.add('translate-x-full');
        return;
    }

    el.style.transition = 'width 0.2s';
    el.style.width = visible ? '288px' : '0px';
    setTimeout(function () { el.style.transition = ''; }, 200);
}

window.toggleFabMenu = function (event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('fab-menu');
    const isHidden = menu.classList.contains('hidden');
    document.getElementById('fab-btn').classList.toggle('rotate-45', isHidden);
    if (isHidden) {
        menu.classList.remove('hidden');
        menu.animate(
            [{ opacity: 0, transform: 'translateY(6px) scale(0.95)' }, { opacity: 1, transform: 'translateY(0) scale(1)' }],
            { duration: 150, easing: 'ease-out' }
        );
    } else {
        menu.animate(
            [{ opacity: 1, transform: 'translateY(0) scale(1)' }, { opacity: 0, transform: 'translateY(6px) scale(0.95)' }],
            { duration: 120, easing: 'ease-in' }
        ).addEventListener('finish', function () {
            menu.classList.add('hidden');
        });
    }
};

export function makeResizable(id, handleId, minWidth, maxWidth) {
    const el = document.getElementById(id);
    const handle = document.getElementById(handleId);
    if (!el || !handle) return;
    let startX, startWidth;
    const STICKY = 32;

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
        if (newWidth < minWidth - STICKY) {
            newWidth = 0;
        } else if (newWidth < minWidth) {
            newWidth = minWidth;
        } else {
            newWidth = Math.min(maxWidth, newWidth);
        }
        el.style.width = newWidth + 'px';
        if (id === 'sidebar-right') showRightbarEmptyState(!rightbarHasChat);
    }

    function onUp() {
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', onUp);
    }
}
