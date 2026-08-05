import { setCurrentChat } from './bubbles.js';
import { setRightbarVisible, setRightbarHasChat } from './sidebar.js';

function workspaceError(id, message) {
    const el = document.getElementById(id);
    if (!el) return;
    if (message) {
        el.textContent = message;
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

function replaceWorkspaceList(html) {
    const container = document.getElementById('workspace-list');
    if (container) container.innerHTML = html;
    const empty = document.getElementById('workspace-empty');
    if (empty) empty.classList.toggle('hidden', html.trim().length > 0);
}

window.openWorkspace = function (el, name, code) {
    document.querySelectorAll('#workspace-list [data-workspace]').forEach(function (item) {
        item.classList.remove('bg-white/5');
    });
    el.classList.add('bg-white/5');
    document.querySelectorAll('[data-name]').forEach(function (item) {
        item.classList.remove('bg-white/5');
    });

    setCurrentChat(null);

    const workspace = document.getElementById('chat-workspace');
    const noChat = document.getElementById('no-chat');
    if (!workspace) return;
    workspace.classList.remove('hidden');
    workspace.classList.add('flex');
    noChat.classList.add('hidden');
    noChat.classList.remove('flex');

    const headerName = document.getElementById('chat-header-name');
    const headerAvatar = document.getElementById('chat-header-avatar');
    const headerStatus = document.getElementById('chat-header-status');
    if (headerName) headerName.textContent = name;
    if (headerAvatar) {
        headerAvatar.innerHTML = '<div class="w-7 h-7 rounded-full bg-[#E091A9]/15 flex items-center justify-center text-[10px] font-medium text-[#E091A9]">' + (name.charAt(0) || '?').toUpperCase() + '</div>';
    }
    if (headerStatus) {
        headerStatus.className = 'flex items-center gap-1 text-[10px] leading-none text-white/30 mt-1';
        headerStatus.innerHTML = '<span class="tracking-widest">' + code + '</span>';
    }

    const emptyRightbar = document.getElementById('rightbar-empty');
    if (emptyRightbar) {
        emptyRightbar.classList.add('hidden');
        emptyRightbar.classList.remove('flex');
    }
    setRightbarVisible(true);
    setRightbarHasChat(true);

    const wsAvatar = document.getElementById('rightbar-ws-avatar');
    const wsName = document.getElementById('rightbar-ws-name');
    const wsCode = document.getElementById('rightbar-ws-code');
    const wsAbout = document.getElementById('rightbar-ws-about');
    if (wsAvatar) wsAvatar.textContent = (name.charAt(0) || '?').toUpperCase();
    if (wsName) wsName.textContent = name;
    if (wsCode) wsCode.textContent = 'Code: ' + code;
    if (wsAbout) wsAbout.textContent = 'Workspace';

    const wsPanel = document.getElementById('rightbar-workspace');
    if (wsPanel) {
        wsPanel.classList.remove('hidden');
        wsPanel.classList.add('flex');
    }

    currentWsCode = code;
    loadWorkspaceMembers(code);

    const workspaceTabs = document.getElementById('workspace-tabs');
    if (workspaceTabs) {
        workspaceTabs.classList.remove('hidden');
        workspaceTabs.classList.add('flex');
    }

    const container = document.getElementById('messages-container');
    if (container) {
        container.innerHTML = '<div class="flex flex-col items-center justify-center h-full gap-2.5">'
            + '<p class="text-xs font-medium text-white/40">' + name + '</p>'
            + '<p class="text-[10px] tracking-widest text-white/25">' + code + '</p>'
            + '<p class="text-[10px] text-white/10">Workspace features coming soon</p>'
            + '</div>';
        container.scrollTop = 0;
    }
};

window.switchWorkspaceRightbarTab = function (kind) {
    const general = document.getElementById('ws-rb-general');
    const members = document.getElementById('ws-rb-members');
    const generalPane = document.getElementById('ws-rb-general-pane');
    const membersPane = document.getElementById('ws-rb-members-pane');
    if (!general || !members || !generalPane || !membersPane) return;
    const isGeneral = kind === 'general';
    general.className = 'flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 -mb-px transition-colors ' + (isGeneral ? 'text-white border-[#E091A9]' : 'text-white/40 border-transparent');
    members.className = 'flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 -mb-px transition-colors ' + (!isGeneral ? 'text-white border-[#E091A9]' : 'text-white/40 border-transparent');
    generalPane.classList.toggle('hidden', !isGeneral);
    membersPane.classList.toggle('hidden', isGeneral);
    if (!isGeneral && currentWsCode) loadWorkspaceMembers(currentWsCode);
};

let currentWsCode = null;

function loadWorkspaceMembers(code) {
    fetch('/workspaces/' + encodeURIComponent(code) + '/members')
        .then(function (response) { return response.json(); })
        .then(function (members) { renderWorkspaceMembers(members); })
        .catch(function () {});
}

function renderWorkspaceMembers(members) {
    const list = document.getElementById('ws-rb-members-list');
    const empty = document.getElementById('ws-rb-members-empty');
    if (!list || !empty) return;
    if (!members || !members.length) {
        empty.classList.remove('hidden');
        empty.classList.add('flex');
        list.innerHTML = '';
        return;
    }
    empty.classList.add('hidden');
    empty.classList.remove('flex');
    const ROLE_CLASS = {
        owner: 'text-yellow-400 bg-yellow-400/10',
        admin: 'text-red-400 bg-red-400/10',
        user: 'text-white/40 bg-white/8',
    };
    list.innerHTML = members.map(function (m) {
        const avatar = m.has_avatar
            ? '<img src="' + m.avatar + '" alt="" class="w-9 h-9 rounded-full object-cover">'
            : '<div class="w-9 h-9 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60">' + m.avatar + '</div>';
        const roleLabel = m.role.charAt(0).toUpperCase() + m.role.slice(1);
        return '<div class="flex items-center gap-2.5 px-3 py-2.5 rounded hover:bg-white/5">'
            + '<div class="relative shrink-0">' + avatar + '</div>'
            + '<div class="flex-1 min-w-0">'
            + '<div class="flex items-center gap-1.5">'
            + '<p class="text-xs font-medium truncate text-white/80">' + escapeHtml(m.name) + '</p>'
            + '<span class="shrink-0 text-[9px] font-medium rounded-full px-2 py-0.5 ' + (ROLE_CLASS[m.role] || ROLE_CLASS.user) + '">' + roleLabel + '</span>'
            + '</div>'
            + '<p class="text-[10px] text-white/30 truncate mt-0.5">@' + escapeHtml(m.username) + '</p>'
            + '</div>'
            + '</div>';
    }).join('');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.openWorkspaceBio = function () {
    const text = document.getElementById('rightbar-ws-about');
    const target = document.getElementById('bio-modal-text');
    if (!text || !target || !text.textContent.trim()) return;
    target.textContent = text.textContent;
    openModal('bio-modal');
};

window.submitCreateWorkspace = function () {
    const input = document.getElementById('workspace-name-input');
    const name = input.value.trim();
    if (!name) {
        workspaceError('create-workspace-error', 'Workspace name is required.');
        return;
    }
    workspaceError('create-workspace-error', '');
    fetch('/workspaces', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ name }),
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (!ok) {
                workspaceError('create-workspace-error', data.message || 'Failed to create workspace.');
                return;
            }
            closeModal('create-workspace-modal');
            input.value = '';
            replaceWorkspaceList(data.html);
            switchTab('workspace');
        })
        .catch(function () {
            workspaceError('create-workspace-error', 'Something went wrong. Please try again.');
        });
};

window.submitJoinWorkspace = function () {
    const input = document.getElementById('workspace-code-input');
    const code = input.value.trim();
    if (code.length !== 8) {
        workspaceError('join-workspace-error', 'Enter the 8-character workspace code.');
        return;
    }
    workspaceError('join-workspace-error', '');
    fetch('/workspaces/join', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ code }),
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (!ok) {
                workspaceError('join-workspace-error', data.message || 'Failed to join workspace.');
                return;
            }
            closeModal('join-workspace-modal');
            input.value = '';
            replaceWorkspaceList(data.html);
            switchTab('workspace');
        })
        .catch(function () {
            workspaceError('join-workspace-error', 'Something went wrong. Please try again.');
        });
};
