import { MOBILE_BREAKPOINT, TAB_BASE, TAB_ACTIVE, TAB_INACTIVE } from './constants.js';
import { DEMO_CONVERSATIONS } from './demo-data.js';
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

    ['media-gallery', 'files-gallery', 'members-gallery'].forEach(function (id) {
        const overlay = document.getElementById(id);
        if (overlay) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
    });

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

    headerAvatar.textContent = chat.avatar;
    document.getElementById('chat-header-name').textContent = name;

    const status = document.getElementById('chat-header-status');
    status.classList.toggle('text-green-400/70', chat.online);
    status.classList.toggle('text-white/30', !chat.online);
    status.innerHTML = chat.group
        ? '<span class="w-1.5 h-1.5 rounded-full bg-white/20 inline-block -mt-0.5"></span> ' + chat.group
        : chat.online
            ? '<span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block -mt-0.5"></span> Online'
            : '<span class="w-1.5 h-1.5 rounded-full bg-white/20 inline-block -mt-0.5"></span> Offline';

    document.getElementById('rightbar-name').textContent = name;
    document.getElementById('rightbar-avatar').textContent = chat.avatar;
    const rightbarStatus = document.getElementById('rightbar-status');
    rightbarStatus.className = 'text-[10px] mt-0.5 ' + (chat.online ? 'text-green-400/70' : 'text-white/30');
    rightbarStatus.textContent = chat.online ? 'Online' : (chat.group || 'Offline');

    const membersSection = document.getElementById('rightbar-members');
    const aboutSection = document.getElementById('rightbar-about');
    const workspaceTabs = document.getElementById('workspace-tabs');
    if (chat.group) {
        aboutSection.classList.add('hidden');
        membersSection.classList.remove('hidden');
        workspaceTabs.classList.remove('hidden');
        workspaceTabs.classList.add('flex');
        document.getElementById('rightbar-members-list').innerHTML = chat.members.map(memberRow).join('');
        setRightbarVisible(false);
    } else {
        membersSection.classList.add('hidden');
        aboutSection.classList.remove('hidden');
        workspaceTabs.classList.add('hidden');
        workspaceTabs.classList.remove('flex');
        document.getElementById('rightbar-about-text').textContent = chat.about || 'No bio yet.';
        setRightbarVisible(true);
    }

    const container = document.getElementById('messages-container');
    setCurrentChat(name);
    container.innerHTML = chat.messages.map(function (message, index) {
        return messageHtml(message, name, index);
    }).join('');
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
        el.classList.toggle('translate-x-full', !visible);
        return;
    }

    el.style.transition = 'width 0.2s';
    el.style.width = visible ? '288px' : '0px';
    setTimeout(function () { el.style.transition = ''; }, 200);
}

function memberInitials(name) {    return name.split(' ').map(function (word) { return word[0]; }).join('');
}

function memberRow(member) {
    const online = DEMO_CONVERSATIONS[member.name] && DEMO_CONVERSATIONS[member.name].online;
    return `
        <div class="flex items-center gap-2 px-1 py-1.5 rounded cursor-pointer hover:bg-white/5 transition-colors" onclick="openConversation('${member.name}', '${memberInitials(member.name)}', ${online})">
            <div class="relative shrink-0">
                <div class="w-7 h-7 rounded-full bg-white/8 flex items-center justify-center text-[9px] font-medium text-white/60">${memberInitials(member.name)}</div>
                ${online ? '<div class="absolute -bottom-0.5 -right-0.5 w-2 h-2 bg-green-500 rounded-full border-2 border-[#0F0F0F]"></div>' : ''}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-medium text-white/80 truncate">${member.name}</p>
                <p class="text-[9px] text-white/30 truncate">${member.role}</p>
            </div>
        </div>`;
}

window.openMembersGallery = function () {
    const seen = {};
    const all = [];
    Object.keys(DEMO_CONVERSATIONS).forEach(function (key) {
        const chat = DEMO_CONVERSATIONS[key];
        if (!chat.group) return;
        chat.members.forEach(function (member) {
            if (!seen[member.name]) {
                seen[member.name] = true;
                all.push(member);
            }
        });
    });
    document.getElementById('members-gallery-list').innerHTML = all.map(memberRow).join('');
    openOverlay('members-gallery');
};

window.closeMembersGallery = function () {
    closeOverlay('members-gallery');
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
