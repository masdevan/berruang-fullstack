import { MOBILE_BREAKPOINT, TAB_BASE, TAB_ACTIVE, TAB_INACTIVE, BUBBLE_ME, BUBBLE_OTHER } from './constants.js';
import { DEMO_CONVERSATIONS } from './demo-data.js';

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

    if (window.innerWidth < MOBILE_BREAKPOINT) {
        el.style.width = '';
        el.classList.toggle('translate-x-full');
        return;
    }

    el.style.transition = 'width 0.2s';
    el.style.width = el.style.width === '0px' ? '288px' : '0px';
    setTimeout(() => el.style.transition = '', 200);
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

window.openConversation = function (name, avatar, online) {
    const chat = DEMO_CONVERSATIONS[name] || { avatar, online, messages: [] };

    const headerAvatar = document.getElementById('chat-header-avatar');
    if (!headerAvatar) {
        window.location.href = '/messages?chat=' + encodeURIComponent(name);
        return;
    }

    if (window.innerWidth < MOBILE_BREAKPOINT) {
        const list = document.getElementById('sidebar-left');
        const area = document.getElementById('message-area');

        list.classList.add('hidden');
        list.style.width = '';
        area.classList.remove('hidden');
        area.classList.add('flex');
    }

    headerAvatar.textContent = chat.avatar;
    document.getElementById('chat-header-name').textContent = name;

    const status = document.getElementById('chat-header-status');
    status.classList.toggle('text-green-400/70', chat.online);
    status.classList.toggle('text-white/30', !chat.online);
    status.innerHTML = chat.online
        ? '<span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block -mt-0.5"></span> Online'
        : '<span class="w-1.5 h-1.5 rounded-full bg-white/20 inline-block -mt-0.5"></span> Offline';

    const container = document.getElementById('messages-container');
    container.innerHTML = '';
    chat.messages.forEach(function (message) {
        const row = document.createElement('div');
        row.className = message.from === 'me' ? 'flex justify-end mb-2' : 'flex justify-start mb-2';
        row.innerHTML = `
            <div class="${message.from === 'me' ? BUBBLE_ME : BUBBLE_OTHER}">
                <p class="text-xs text-white/85 leading-relaxed">${message.text}</p>
                <p class="text-[9px] text-white/25 text-right mt-0.5">${message.time}</p>
            </div>
        `;
        container.appendChild(row);
    });
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

export function makeResizable(id, handleId, minWidth, maxWidth) {
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
