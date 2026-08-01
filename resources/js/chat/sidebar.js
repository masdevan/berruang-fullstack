import { MOBILE_BREAKPOINT, TAB_BASE, TAB_ACTIVE, TAB_INACTIVE } from './constants.js';
import { messageHtml, setCurrentChat } from './bubbles.js';

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

window.openConversation = function (name, avatar, online, about, customName, realName, username, hasAvatar) {

    const headerAvatar = document.getElementById('chat-header-avatar');
    if (!headerAvatar) {
        window.location.href = '/messages?chat=' + encodeURIComponent(name);
        return;
    }

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
    }

    const AVATAR_IMG = hasAvatar
        ? '<img src="' + avatar + '" class="w-full h-full object-cover rounded-full">'
        : avatar;
    headerAvatar.innerHTML = AVATAR_IMG;
    document.getElementById('chat-header-name').textContent = name;

    const status = document.getElementById('chat-header-status');
    status.classList.toggle('text-green-400/70', online);
    status.classList.toggle('text-white/30', !online);
    status.innerHTML = online
        ? '<span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block -mt-0.5"></span> Online'
        : '<span class="w-1.5 h-1.5 rounded-full bg-white/20 inline-block -mt-0.5"></span> Offline';

    const customNameEl = document.getElementById('rightbar-custom-name');
    const realNameEl = document.getElementById('rightbar-real-name');
    const usernameEl = document.getElementById('rightbar-username');
    customNameEl.textContent = name;
    customNameEl.classList.toggle('hidden', !customName);
    realNameEl.textContent = realName;
    realNameEl.classList.toggle('hidden', !realName);
    usernameEl.textContent = '@' + username;
    usernameEl.classList.toggle('hidden', !username);
    document.getElementById('rightbar-avatar').innerHTML = AVATAR_IMG;
    const rightbarDot = document.getElementById('rightbar-online-dot');
    rightbarDot.classList.toggle('bg-green-500', online);
    rightbarDot.classList.toggle('bg-white/20', !online);
    document.getElementById('rightbar-about-text').textContent = about;
    rightbarHasChat = true;
    showRightbarEmptyState(false);
    setRightbarVisible(true);

    const container = document.getElementById('messages-container');
    setCurrentChat(name);
    container.innerHTML = '';
    container.scrollTop = container.scrollHeight;
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
