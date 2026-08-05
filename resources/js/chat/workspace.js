import { setCurrentChat } from './bubbles.js';
import { setRightbarVisible } from './sidebar.js';

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
        emptyRightbar.classList.remove('hidden');
        emptyRightbar.classList.add('flex');
    }
    setRightbarVisible(false);

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
