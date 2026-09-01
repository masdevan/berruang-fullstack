import { setCurrentChat } from './bubbles.js';
import { setRightbarVisible, setRightbarHasChat } from './sidebar.js';
import { initAvatarPicker, setAvatarTarget } from '../avatar-picker.js';
import { MOBILE_BREAKPOINT } from './constants.js';

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
    if (window.recalcWsUnreadTotal) window.recalcWsUnreadTotal();
}

window.openWorkspace = function (el, name, code, created) {
    const workspace = document.getElementById('chat-workspace');
    if (!workspace) {
        window.location.href = '/messages?ws=' + encodeURIComponent(code);
        return;
    }
    if (window.leaveWorkspaceChat) window.leaveWorkspaceChat();

    const chatInput = document.getElementById('message-input');
    if (chatInput) {
        chatInput.value = '';
        chatInput.style.height = 'auto';
    }

    document.querySelectorAll('#workspace-list [data-workspace]').forEach(function (item) {
        item.classList.remove('bg-white/5');
    });
    el.classList.add('bg-white/5');
    document.querySelectorAll('#tab-pane-chat [data-name]').forEach(function (item) {
        item.classList.remove('bg-white/5');
    });

    setCurrentChat(null);

    const noChat = document.getElementById('no-chat');
    workspace.classList.remove('hidden');
    workspace.classList.add('flex');
    noChat.classList.add('hidden');
    noChat.classList.remove('flex');

    if (window.innerWidth < MOBILE_BREAKPOINT) {
        const list = document.getElementById('sidebar-left');
        const area = document.getElementById('message-area');
        if (list && area) {
            list.classList.add('hidden');
            list.style.width = '';
            area.classList.remove('hidden');
            area.classList.add('flex');
        }
        if (!new URLSearchParams(window.location.search).get('ws')) {
            history.pushState({ ws: code }, '', '?ws=' + encodeURIComponent(code));
        }
    }

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
    const wsCreated = document.getElementById('rightbar-ws-created');
    const wsAbout = document.getElementById('rightbar-ws-about');
    if (wsAvatar) {
        wsAvatar.innerHTML = el.dataset.avatar
            ? '<img src="' + el.dataset.avatar + '" alt="" class="w-full h-full rounded-full object-cover">'
            : (name.charAt(0) || '?').toUpperCase();
        if (el.dataset.fullAvatar) {
            wsAvatar.onclick = function () { window.openMediaModal(el.dataset.fullAvatar); };
            wsAvatar.title = 'View workspace photo';
            wsAvatar.classList.add('cursor-pointer');
        } else {
            wsAvatar.onclick = null;
            wsAvatar.title = '';
            wsAvatar.classList.remove('cursor-pointer');
        }
    }
    if (wsName) wsName.textContent = name;
    if (wsCode) wsCode.textContent = 'Code: ' + code;
    if (wsCreated) wsCreated.textContent = 'Created ' + (created || '');
    if (wsAbout) wsAbout.textContent = el.dataset.bio || 'Workspace';
    currentWsConfig = { name: name, code: code, bio: el.dataset.bio || '', avatar: el.dataset.avatar || '' };

    const wsPanel = document.getElementById('rightbar-workspace');
    if (wsPanel) {
        wsPanel.classList.remove('hidden');
        wsPanel.classList.add('flex');
    }

    const configureBtn = document.getElementById('rightbar-ws-configure');
    if (configureBtn) {
        configureBtn.classList.toggle('hidden', el.dataset.myRole !== 'owner' && el.dataset.myRole !== 'admin');
    }

    const addMemberHeader = document.getElementById('ws-rb-members-header');
    if (addMemberHeader) {
        const isManager = el.dataset.myRole === 'owner' || el.dataset.myRole === 'admin';
        addMemberHeader.classList.toggle('hidden', !isManager);
        addMemberHeader.classList.toggle('flex', isManager);
    }

    currentWsRole = el.dataset.myRole || 'user';
    if (bulkMode) toggleBulkKick(false);

    currentWsCode = code;
    loadWorkspaceMembers(code);

    // hide sub header (workspace-tabs)
    // const workspaceTabs = document.getElementById('workspace-tabs');
    // if (workspaceTabs) {
    //     workspaceTabs.classList.remove('hidden');
    //     workspaceTabs.classList.add('flex');
    // }

    const container = document.getElementById('messages-container');
    if (container) {
        container.innerHTML = '';
        container.scrollTop = 0;
    }
    window.currentWorkspaceCode = code;
    const inputBar = document.getElementById('chat-input-bar');
    if (inputBar) inputBar.classList.remove('hidden');
    if (window.loadWorkspaceHistory) window.loadWorkspaceHistory(code);
};

window.switchWorkspaceRightbarTab = function (kind) {
    const general = document.getElementById('ws-rb-general');
    const members = document.getElementById('ws-rb-members');
    const generalPane = document.getElementById('ws-rb-general-pane');
    const membersPane = document.getElementById('ws-rb-members-pane');
    if (!general || !members || !generalPane || !membersPane) return;
    const isGeneral = kind === 'general';
    general.className = 'flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 transition-colors ' + (isGeneral ? 'text-white border-[#E091A9]' : 'text-white/40 border-white/6');
    members.className = 'flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 transition-colors ' + (!isGeneral ? 'text-white border-[#E091A9]' : 'text-white/40 border-white/6');
    generalPane.classList.toggle('hidden', !isGeneral);
    membersPane.classList.toggle('hidden', isGeneral);
    if (!isGeneral && currentWsCode) loadWorkspaceMembers(currentWsCode);
};

let currentWsCode = null;
let currentWsConfig = { name: '', code: '', bio: '', avatar: '' };
let currentWsRole = 'user';
let currentWsMembers = [];
let wsAvatarFile = null;
let wsSaveConfirmed = false;
let bulkMode = false;
let bulkSelected = {};
let bulkConfirmOpen = false;
let pendingMemberAction = null;
let leaveSuccessorId = null;
let currentViewedMember = null;

const CROWN_SVG = '<svg viewBox="0 0 24 24" fill="currentColor" class="w-2.5 h-2.5"><path d="M5 16l-2.6-6.9a.5.5 0 0 1 .83-.54L7 11.2l3.8-5.7a.5.5 0 0 1 .84 0l3.8 5.7 3.77-2.44a.5.5 0 0 1 .83.54L17 16H5zm-1 2.5h14a1 1 0 1 1 0 2H4a1 1 0 1 1 0-2z"/></svg>';
const BULK_KICK_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>';

function setWsConfigAvatar(url) {
    const img = document.getElementById('ws-config-avatar');
    const fallback = document.getElementById('ws-config-avatar-fallback');
    if (!img || !fallback) return;
    if (url) {
        img.src = url;
        img.classList.remove('hidden');
        fallback.classList.add('hidden');
    } else {
        img.classList.add('hidden');
        fallback.classList.remove('hidden');
    }
}

function onWorkspaceConfigAvatarUpload(blob) {
    wsAvatarFile = blob;
    const img = document.getElementById('ws-config-avatar');
    if (img) img.classList.remove('hidden');
    const fallback = document.getElementById('ws-config-avatar-fallback');
    if (fallback) fallback.classList.add('hidden');
}

initAvatarPicker({
    previewId: 'ws-config-avatar',
    onUpload: onWorkspaceConfigAvatarUpload,
});

window.openWorkspaceConfig = function () {
    const panel = document.getElementById('rightbar-workspace-config');
    const bio = document.getElementById('ws-config-bio');
    const codeInput = document.getElementById('ws-config-code');
    const error = document.getElementById('ws-config-error');
    if (!panel || !bio || !codeInput) return;
    setAvatarTarget('ws-config-avatar', onWorkspaceConfigAvatarUpload);
    bio.value = currentWsConfig.bio || '';
    codeInput.value = currentWsConfig.code;
    wsAvatarFile = null;
    const fallback = document.getElementById('ws-config-avatar-fallback');
    if (fallback) fallback.textContent = (currentWsConfig.name.charAt(0) || '?').toUpperCase();
    setWsConfigAvatar(currentWsConfig.avatar || '');
    if (error) error.classList.add('hidden');
    panel.classList.remove('hidden');
    panel.classList.add('flex');
    panel.animate(
        [{ transform: 'translateX(100%)' }, { transform: 'translateX(0)' }],
        { duration: 180, easing: 'ease-out' }
    );
};

window.confirmWorkspaceCodeChange = function () {
    closeModal('ws-code-confirm-modal');
    wsSaveConfirmed = true;
    saveWorkspaceConfig();
};

window.saveWorkspaceConfig = function () {
    const bio = document.getElementById('ws-config-bio');
    const codeInput = document.getElementById('ws-config-code');
    const error = document.getElementById('ws-config-error');
    if (!bio || !codeInput || !currentWsConfig.code) return;
    if (error) error.classList.add('hidden');

    if (!wsSaveConfirmed && codeInput.value !== currentWsConfig.code) {
        openModal('ws-code-confirm-modal');
        return;
    }
    wsSaveConfirmed = false;

    const form = new FormData();
    form.append('bio', bio.value);
    form.append('code', codeInput.value);
    if (wsAvatarFile) form.append('avatar', wsAvatarFile);

    fetch('/workspaces/' + encodeURIComponent(currentWsConfig.code) + '/configure', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: form,
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (!ok) {
                if (error) {
                    error.textContent = data.message || 'Failed to save changes.';
                    error.classList.remove('hidden');
                }
                return;
            }
            closeWorkspaceConfig();
            currentWsConfig.code = data.code;
            currentWsConfig.bio = data.bio || '';
            currentWsConfig.avatar = data.avatar || '';
            wsAvatarFile = null;
            replaceWorkspaceList(data.html);
            updateWorkspaceInfo(currentWsConfig.name, data.code, data.bio || '', data.avatar || '');
        })
        .catch(function () {
            if (error) {
                error.textContent = 'Something went wrong. Please try again.';
                error.classList.remove('hidden');
            }
        });
};

function updateWorkspaceInfo(name, code, bio, avatar) {
    const wsName = document.getElementById('rightbar-ws-name');
    const wsCode = document.getElementById('rightbar-ws-code');
    const wsAbout = document.getElementById('rightbar-ws-about');
    const wsAvatar = document.getElementById('rightbar-ws-avatar');
    const headerName = document.getElementById('chat-header-name');
    const headerStatus = document.getElementById('chat-header-status');
    if (wsName) wsName.textContent = name;
    if (wsCode) wsCode.textContent = 'Code: ' + code;
    if (wsAbout) wsAbout.textContent = bio || 'Workspace';
    if (wsAvatar) {
        wsAvatar.innerHTML = avatar
            ? '<img src="' + avatar + '" alt="" class="w-full h-full rounded-full object-cover">'
            : (name.charAt(0) || '?').toUpperCase();
    }
    if (headerName) headerName.textContent = name;
    if (headerStatus) {
        headerStatus.className = 'flex items-center gap-1 text-[10px] leading-none text-white/30 mt-1';
        headerStatus.innerHTML = '<span class="tracking-widest">' + code + '</span>';
    }
    const container = document.getElementById('messages-container');
    if (container) {
        const codeEl = container.querySelector('.tracking-widest');
        if (codeEl) codeEl.textContent = 'Code: ' + code;
    }
}

window.closeWorkspaceConfig = function () {
    const panel = document.getElementById('rightbar-workspace-config');
    if (!panel || panel.classList.contains('hidden')) return;
    panel.animate(
        [{ transform: 'translateX(0)' }, { transform: 'translateX(100%)' }],
        { duration: 150, easing: 'ease-in' }
    ).addEventListener('finish', function () {
        panel.classList.add('hidden');
        panel.classList.remove('flex');
    });
};

window.rollWorkspaceCode = function () {
    const input = document.getElementById('ws-config-code');
    if (!input) return;
    const CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += CHARS[Math.floor(Math.random() * CHARS.length)];
    }
    input.value = code;
};

function loadWorkspaceMembers(code) {
    fetch('/workspaces/' + encodeURIComponent(code) + '/members')
        .then(function (response) { return response.json(); })
        .then(function (members) { renderWorkspaceMembers(members); })
        .catch(function () {});
}

function applyRoleUi(role) {
    const isManager = role === 'owner' || role === 'admin';
    const addMemberHeader = document.getElementById('ws-rb-members-header');
    if (addMemberHeader) {
        addMemberHeader.classList.toggle('hidden', !isManager);
        addMemberHeader.classList.toggle('flex', isManager);
    }
    const configureBtn = document.getElementById('rightbar-ws-configure');
    if (configureBtn) configureBtn.classList.toggle('hidden', !isManager);
}

function renderWorkspaceMembers(members) {
    const list = document.getElementById('ws-rb-members-list');
    const empty = document.getElementById('ws-rb-members-empty');
    if (!list || !empty) return;
    currentWsMembers = members || [];
    if (!members || !members.length) {
        empty.classList.remove('hidden');
        empty.classList.add('flex');
        list.innerHTML = '';
        updateBulkCount();
        return;
    }
    empty.classList.add('hidden');
    empty.classList.remove('flex');
    window.wsReadPositions = {};
    members.forEach(function (m) {
        window.wsReadPositions[m.id] = m.last_read_message_id || 0;
    });
    const canManage = currentWsRole === 'owner' || currentWsRole === 'admin';
    const ROLE_CLASS = {
        owner: 'text-yellow-400 bg-yellow-400/10',
        admin: 'text-red-400 bg-red-400/10',
        user: 'text-white/40 bg-white/8',
    };
    list.innerHTML = members.map(function (m) {
        const selectable = bulkMode && !m.creator && !m.is_me;
        const avatar = m.has_avatar
            ? '<img src="' + m.avatar + '" alt="" class="w-9 h-9 rounded-full object-cover">'
            : '<div class="w-9 h-9 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60">' + m.avatar + '</div>';
        const checkbox = selectable
            ? '<button type="button" class="shrink-0 w-4 h-4 rounded-sm border border-white/20 flex items-center justify-center cursor-pointer mr-1 ' + (bulkSelected[m.id] ? 'bg-[#E091A9] border-[#E091A9]' : '') + '" data-bulk-check="' + m.id + '">'
                + (bulkSelected[m.id] ? '<svg viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="1.8" class="w-2.5 h-2.5 text-[#0A0A0A]"><path stroke-linecap="round" stroke-linejoin="round" d="M1 4.5L4.5 8L11 1"/></svg>' : '')
                + '</button>'
            : '';
        const crown = m.creator ? '<span class="shrink-0 text-yellow-400" title="Workspace creator">' + CROWN_SVG + '</span>' : '';
        const roleLabel = m.role.charAt(0).toUpperCase() + m.role.slice(1);
        const click = bulkMode
            ? (selectable ? 'toggleBulkSelect(' + m.id + ')' : '')
            : (m.is_me ? '' : 'openMemberProfile(' + m.id + ')');
        const ctx = !m.is_me ? 'openMemberContextMenu(event, ' + m.id + ')' : '';
        return '<div class="flex items-center gap-2.5 px-3 py-2.5 hover:bg-white/5 cursor-pointer" data-member-id="' + m.id + '" data-member-username="' + m.username + '" onclick="' + click + '" oncontextmenu="' + ctx + '">'
            + checkbox
            + '<div class="relative shrink-0">' + avatar + '</div>'
            + '<div class="flex-1 min-w-0">'
            + '<div class="flex items-center gap-1.5">'
            + '<p class="text-xs font-medium truncate text-white/80">' + escapeHtml(m.name) + '</p>'
            + '<span class="shrink-0 inline-flex items-center gap-1 text-[9px] font-medium rounded-full px-2 py-0.5 ' + (ROLE_CLASS[m.role] || ROLE_CLASS.user) + '">' + crown + roleLabel + '</span>'
            + '</div>'
            + '<p class="text-[10px] text-white/30 truncate mt-0.5">@' + escapeHtml(m.username) + '</p>'
            + '</div>'
            + (!m.is_me && !bulkMode ? '<button type="button" onclick="openMemberContextMenu(event, ' + m.id + ')" class="shrink-0 w-7 h-7 flex items-center justify-center rounded-sm text-white/30 hover:text-white hover:bg-white/5 transition-colors cursor-pointer" title="More">'
                + '<svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>'
                + '</button>' : '')
            + '</div>';
    }).join('');
    const me = members.find(function (m) { return m.is_me; });
    if (me && me.role !== currentWsRole) {
        currentWsRole = me.role;
        applyRoleUi(me.role);
        if (bulkMode) toggleBulkKick(false);
        const row = document.querySelector('[data-workspace="' + currentWsCode + '"]');
        if (row) row.setAttribute('data-my-role', me.role);
    }
    updateBulkCount();
}

function renderBulkHeader() {
    const addBtn = document.getElementById('ws-add-member-btn');
    const bulkBtn = document.getElementById('ws-bulk-kick-btn');
    const label = document.getElementById('ws-bulk-label');
    if (addBtn) addBtn.classList.toggle('hidden', bulkMode);
    if (!bulkBtn) return;
    const count = Object.keys(bulkSelected).length;
    const showCancel = bulkMode && (bulkConfirmOpen || count === 0);
    if (label) label.textContent = !bulkMode ? 'Remove' : (showCancel ? 'Cancel' : 'Remove (' + count + ')');
    const pink = bulkMode && !showCancel;
    bulkBtn.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-sm text-[10px] font-medium transition-colors cursor-pointer '
        + (pink ? 'bg-[#E091A9] text-[#0A0A0A] hover:bg-[#E8A8BC]' : 'bg-white/5 hover:bg-white/10 text-white/50 hover:text-white');
}

window.toggleBulkKick = function (on) {
    bulkMode = !!on;
    bulkSelected = {};
    bulkConfirmOpen = false;
    renderBulkHeader();
    renderWorkspaceMembers(currentWsMembers);
};

window.toggleBulkSelect = function (id) {
    if (bulkSelected[id]) {
        delete bulkSelected[id];
    } else {
        bulkSelected[id] = true;
    }
    const check = document.querySelector('[data-bulk-check="' + id + '"]');
    if (check) {
        check.classList.toggle('bg-[#E091A9]', !!bulkSelected[id]);
        check.classList.toggle('border-[#E091A9]', !!bulkSelected[id]);
        check.innerHTML = bulkSelected[id] ? '<svg viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="1.8" class="w-2.5 h-2.5 text-[#0A0A0A]"><path stroke-linecap="round" stroke-linejoin="round" d="M1 4.5L4.5 8L11 1"/></svg>' : '';
    }
    updateBulkCount();
};

function updateBulkCount() {
    renderBulkHeader();
}

window.confirmBulkKick = function () {
    const ids = Object.keys(bulkSelected).map(Number);
    if (!ids.length) return;
    bulkConfirmOpen = true;
    pendingMemberAction = { type: 'bulk-kick', ids: ids, name: ids.length + ' members' };
    const text = document.getElementById('ws-member-action-text');
    if (text) text.textContent = 'Remove ' + ids.length + ' selected member(s) from this workspace?';
    const modalCancel = document.getElementById('ws-member-action-cancel');
    if (modalCancel) modalCancel.classList.add('hidden');
    renderBulkHeader();
    openModal('ws-member-action-modal');
};

window.closeBulkKickConfirm = function () {
    const wasBulkKick = pendingMemberAction && pendingMemberAction.type === 'bulk-kick';
    pendingMemberAction = null;
    bulkConfirmOpen = false;
    closeModal('ws-member-action-modal');
    const modalCancel = document.getElementById('ws-member-action-cancel');
    if (modalCancel) modalCancel.classList.remove('hidden');
    if (wasBulkKick) toggleBulkKick(false);
    else renderBulkHeader();
};

const wsMembersHeader = document.getElementById('ws-rb-members-header');
if (wsMembersHeader) {
    wsMembersHeader.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-bulk]');
        if (!btn || btn.dataset.bulk !== 'toggle') return;
        if (bulkConfirmOpen) {
            closeBulkKickConfirm();
        } else if (bulkMode && Object.keys(bulkSelected).length === 0) {
            toggleBulkKick(false);
        } else if (bulkMode) {
            confirmBulkKick();
        } else {
            toggleBulkKick(true);
        }
    });
}

function memberById(id) {
    return currentWsMembers.find(function (m) { return m.id === Number(id); });
}

window.openMemberContextMenu = function (e, id) {
    e.preventDefault();
    e.stopPropagation();
    const m = memberById(id);
    const menu = document.getElementById('ws-member-context-menu');
    if (!m || !menu) return;
    const canManage = currentWsRole === 'owner' || currentWsRole === 'admin';
    const items = [];
    if (canManage && !m.creator) {
        if (m.role === 'user') {
            items.push({ label: 'Promote to owner', action: 'promote' });
        } else {
            items.push({ label: 'Demote to user', action: 'demote' });
        }
        items.push({ label: 'Kick member', action: 'kick' });
    } else {
        items.push({ label: 'View profile', action: 'profile' });
        items.push({ label: 'Direct chat', action: 'chat' });
    }
    menu.innerHTML = items.map(function (item) {
        return '<button type="button" class="w-full text-left px-3 py-2 text-[11px] text-white/80 hover:bg-white/5 transition-colors cursor-pointer" data-action="' + item.action + '" data-id="' + m.id + '">' + item.label + '</button>';
    }).join('');
    menu.style.left = Math.min(e.clientX, window.innerWidth - 160) + 'px';
    menu.style.top = Math.min(e.clientY, window.innerHeight - 120) + 'px';
    menu.classList.remove('hidden');
    menu.classList.add('flex');
};

function hideMemberContextMenu() {
    const menu = document.getElementById('ws-member-context-menu');
    if (menu) {
        menu.classList.add('hidden');
        menu.classList.remove('flex');
    }
}

window.confirmMemberAction = function (action, id) {
    const m = memberById(id);
    if (!m) return;
    pendingMemberAction = { type: action, id: m.id, name: m.name };
    const text = document.getElementById('ws-member-action-text');
    const label = action === 'promote' ? 'Promote' : action === 'demote' ? 'Demote' : 'Remove';
    if (text) {
        text.textContent = label + ' ' + m.name + (action === 'promote' ? ' to owner?' : action === 'demote' ? ' to user?' : ' from this workspace?');
        text.classList.remove('text-red-400');
    }
    hideMemberContextMenu();
    const modalCancel = document.getElementById('ws-member-action-cancel');
    if (modalCancel) modalCancel.classList.remove('hidden');
    openModal('ws-member-action-modal');
};

window.runMemberAction = function () {
    if (!pendingMemberAction) return;
    const action = pendingMemberAction;
    closeModal('ws-member-action-modal');

    if (action.type === 'leave') {
        pendingMemberAction = null;
        leaveWorkspace(null);
        return;
    }

    let url, body;
    if (action.type === 'bulk-kick') {
        url = '/workspaces/' + encodeURIComponent(currentWsCode) + '/members/kick';
        body = JSON.stringify({ ids: action.ids });
    } else if (action.type === 'kick') {
        url = '/workspaces/' + encodeURIComponent(currentWsCode) + '/members/kick';
        body = JSON.stringify({ ids: [action.id] });
    } else {
        url = '/workspaces/' + encodeURIComponent(currentWsCode) + '/members/' + action.id + '/' + action.type;
        body = JSON.stringify({});
    }

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: body,
    })
        .then(function (response) {
            return response.text().then(function (text) {
                var data = null;
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    data = { message: 'Server error (HTTP ' + response.status + ').' };
                }
                return { ok: response.ok, data: data };
            });
        })
        .then(function ({ ok, data }) {
            if (!ok) {
                showMemberActionError(data.message || 'Action failed.');
                return;
            }
            pendingMemberAction = null;
            if (action.type === 'bulk-kick') toggleBulkKick(false);
            loadWorkspaceMembers(currentWsCode);
        })
        .catch(function (err) {
            showMemberActionError('Something went wrong. Please try again.' + (err && err.message ? ' (' + err.message + ')' : ''));
        });
};

function showMemberActionError(message) {
    const text = document.getElementById('ws-member-action-text');
    const modalCancel = document.getElementById('ws-member-action-cancel');
    if (modalCancel) modalCancel.classList.remove('hidden');
    if (text) {
        text.textContent = message;
        text.classList.add('text-red-400');
        openModal('ws-member-action-modal');
    }
}

window.openMemberProfile = function (id) {
    const m = memberById(id);
    if (!m) return;
    currentViewedMember = m;
    const avatar = document.getElementById('mp-avatar');
    const fallback = document.getElementById('mp-avatar-fallback');
    if (avatar && fallback) {
        if (m.has_avatar) {
            avatar.src = m.avatar;
            avatar.classList.remove('hidden');
            fallback.classList.add('hidden');
        } else {
            avatar.classList.add('hidden');
            fallback.textContent = m.name.charAt(0).toUpperCase();
            fallback.classList.remove('hidden');
        }
    }
    const name = document.getElementById('mp-name');
    const username = document.getElementById('mp-username');
    const bio = document.getElementById('mp-bio');
    const joined = document.getElementById('mp-joined');
    const role = document.getElementById('mp-role');
    if (name) name.textContent = m.name;
    if (username) username.textContent = '@' + m.username;
    if (bio) {
        bio.textContent = m.bio || 'No bio yet';
        bio.classList.toggle('text-white/25', !m.bio);
        bio.classList.toggle('text-white/60', !!m.bio);
    }
    if (joined) joined.textContent = 'Joined ' + (m.joined || '-');
    if (role) {
        role.textContent = m.creator ? 'Workspace creator' : (m.role.charAt(0).toUpperCase() + m.role.slice(1));
        role.className = 'mt-1.5 inline-flex items-center text-[9px] font-medium rounded-full px-2 py-0.5 ' + (m.creator ? 'text-yellow-400 bg-yellow-400/10' : m.role === 'owner' ? 'text-yellow-400 bg-yellow-400/10' : 'text-white/40 bg-white/8');
    }
    openModal('member-profile-modal');
};

window.directChatWithMember = function () {
    const m = currentViewedMember;
    if (!m) return;
    closeModal('member-profile-modal');
    const openChat = function () {
        window.switchTab('chat');
        window.openConversation(m.name, m.avatar, 'offline', m.bio || '', '', m.name, m.username, m.has_avatar);
    };
    if (document.querySelector('[data-username="' + m.username + '"]')) {
        openChat();
        return;
    }
    fetch('/contacts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ username: m.username }),
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (ok && data.html) {
                const list = document.querySelector('#tab-pane-chat .flex-1.overflow-y-auto');
                if (list) {
                    const empty = list.querySelector('.empty-state');
                    if (empty) empty.remove();
                    list.insertAdjacentHTML('afterbegin', data.html);
                }
            }
            openChat();
        })
        .catch(function () { openChat(); });
};

window.openLeaveWorkspace = function () {
    const me = currentWsMembers.find(function (m) { return m.is_me; });
    const isCreator = me && me.creator;
    if (isCreator) {
        const hasOthers = currentWsMembers.some(function (m) { return !m.is_me; });
        if (!hasOthers) {
            const text = document.getElementById('ws-member-action-text');
            pendingMemberAction = { type: 'leave' };
            if (text) text.textContent = 'You are the only member. Leave this workspace?';
            const modalCancel = document.getElementById('ws-member-action-cancel');
            if (modalCancel) modalCancel.classList.remove('hidden');
            openModal('ws-member-action-modal');
            return;
        }
        populateLeaveDelegate();
        openModal('ws-leave-delegate-modal');
        return;
    }
    const text = document.getElementById('ws-member-action-text');
    pendingMemberAction = { type: 'leave' };
    if (text) text.textContent = 'Leave this workspace? You can rejoin later with the workspace code.';
    openModal('ws-member-action-modal');
};

function populateLeaveDelegate() {
    const list = document.getElementById('ws-leave-delegate-list');
    if (!list) return;
    leaveSuccessorId = null;
    const options = currentWsMembers.filter(function (m) { return !m.is_me; });
    if (!options.length) {
        list.innerHTML = '<p class="text-[10px] text-white/30 text-center py-4">No other members to delegate to.</p>';
        const confirmBtn = document.getElementById('ws-leave-delegate-confirm');
        if (confirmBtn) confirmBtn.classList.add('hidden');
        return;
    }
    const confirmBtn = document.getElementById('ws-leave-delegate-confirm');
    if (confirmBtn) confirmBtn.classList.remove('hidden');
    list.innerHTML = options.map(function (m) {
        const avatar = m.has_avatar
            ? '<img src="' + m.avatar + '" alt="" class="w-8 h-8 rounded-full object-cover">'
            : '<div class="w-8 h-8 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60">' + m.name.charAt(0).toUpperCase() + '</div>';
        return '<button type="button" class="w-full flex items-center gap-2.5 px-3 py-2 rounded hover:bg-white/5 transition-colors cursor-pointer" onclick="selectLeaveSuccessor(' + m.id + ')">'
            + avatar
            + '<div class="flex-1 min-w-0 text-left">'
            + '<p class="text-xs font-medium truncate text-white/80">' + escapeHtml(m.name) + '</p>'
            + '<p class="text-[10px] text-white/30 truncate">@' + escapeHtml(m.username) + '</p>'
            + '</div>'
            + '<span class="shrink-0 text-[9px] font-medium rounded-full px-2 py-0.5 ' + (m.role === 'owner' ? 'text-yellow-400 bg-yellow-400/10' : 'text-white/40 bg-white/8') + '">' + (m.role.charAt(0).toUpperCase() + m.role.slice(1)) + '</span>'
            + '</button>';
    }).join('');
}

window.selectLeaveSuccessor = function (id) {
    leaveSuccessorId = id;
    document.querySelectorAll('#ws-leave-delegate-list button').forEach(function (btn) {
        btn.classList.remove('bg-white/5');
    });
    const btn = document.querySelector('#ws-leave-delegate-list button[onclick="selectLeaveSuccessor(' + id + ')"]');
    if (btn) btn.classList.add('bg-white/5');
};

window.confirmLeaveDelegation = function () {
    if (!leaveSuccessorId) return;
    leaveWorkspace(leaveSuccessorId);
};

window.confirmLeaveWorkspace = function () {
    leaveWorkspace(null);
};

function leaveWorkspace(successorId) {
    const payload = successorId ? JSON.stringify({ successor_id: successorId }) : '{}';
    fetch('/workspaces/' + encodeURIComponent(currentWsCode) + '/leave', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: payload,
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            closeModal('ws-leave-delegate-modal');
            closeModal('ws-member-action-modal');
            if (!ok) {
                const text = document.getElementById('ws-member-action-text');
                if (text) {
                    text.textContent = data.message || 'Failed to leave workspace.';
                    text.classList.add('text-red-400');
                    openModal('ws-member-action-modal');
                }
                return;
            }
            replaceWorkspaceList(data.html);
            closeWorkspaceView();
        })
        .catch(function () {});
}

function closeWorkspaceView() {
    if (window.leaveWorkspaceChat) window.leaveWorkspaceChat();
    setCurrentChat(null);
    const workspace = document.getElementById('chat-workspace');
    const noChat = document.getElementById('no-chat');
    if (workspace) {
        workspace.classList.add('hidden');
        workspace.classList.remove('flex');
    }
    if (noChat) {
        noChat.classList.remove('hidden');
        noChat.classList.add('flex');
    }
    const wsPanel = document.getElementById('rightbar-workspace');
    if (wsPanel) {
        wsPanel.classList.add('hidden');
        wsPanel.classList.remove('flex');
    }
    const workspaceTabs = document.getElementById('workspace-tabs');
    if (workspaceTabs) {
        workspaceTabs.classList.add('hidden');
        workspaceTabs.classList.remove('flex');
    }
    const rightbarEmpty = document.getElementById('rightbar-empty');
    if (rightbarEmpty) {
        rightbarEmpty.classList.remove('hidden');
        rightbarEmpty.classList.add('flex');
    }
    if (window.innerWidth < MOBILE_BREAKPOINT) {
        if (history.state && history.state.ws === currentWsCode) {
            history.back();
        } else {
            backToConversations();
        }
    }
}

window.removeWorkspaceRow = function (code) {
    const row = document.querySelector('[data-workspace="' + code + '"]');
    if (row) row.remove();
    const container = document.getElementById('workspace-list');
    const empty = document.getElementById('workspace-empty');
    if (container && empty) {
        empty.classList.toggle('hidden', container.children.length > 0);
    }
    if (window.recalcWsUnreadTotal) window.recalcWsUnreadTotal();
    if (currentWsCode === code) closeWorkspaceView();
};

document.addEventListener('click', function (e) {
    const item = e.target.closest('#ws-member-context-menu [data-action]');
    if (item) {
        const action = item.dataset.action;
        const id = Number(item.dataset.id);
        hideMemberContextMenu();
        if (action === 'profile') {
            openMemberProfile(id);
        } else if (action === 'chat') {
            currentViewedMember = memberById(id);
            directChatWithMember();
        } else {
            confirmMemberAction(action, id);
        }
        return;
    }
    if (!e.target.closest('#ws-member-context-menu')) hideMemberContextMenu();
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') hideMemberContextMenu();
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function pendingWorkspaceRow(d) {
    const avatar = d.avatar
        ? '<img src="' + d.avatar + '" alt="" class="shrink-0 w-9 h-9 rounded-full object-cover opacity-60">'
        : '<div class="shrink-0 w-9 h-9 rounded-full bg-[#E091A9]/10 flex items-center justify-center text-xs font-medium text-[#E091A9]/60">' + (d.name.charAt(0) || '?').toUpperCase() + '</div>';
    return '<div data-workspace="' + d.code + '" class="flex items-center gap-2.5 px-3 py-2.5">'
        + avatar
        + '<div class="flex-1 min-w-0">'
        + '<div class="flex items-center justify-between">'
        + '<p class="text-xs font-medium truncate text-white/70">' + escapeHtml(d.name) + '</p>'
        + '<p class="text-[9px] text-[#E091A9]/70 shrink-0 ml-2">' + escapeHtml(d.inviter_name || 'Someone') + ' invited you</p>'
        + '</div>'
        + '<div class="flex items-center gap-1.5 mt-1.5">'
        + '<button type="button" onclick="confirmWorkspaceInvite(\'' + d.code + '\', false)" class="px-2.5 py-1 rounded-sm bg-white/5 hover:bg-white/10 text-[10px] font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Reject</button>'
        + '<button type="button" onclick="confirmWorkspaceInvite(\'' + d.code + '\', true)" class="px-2.5 py-1 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[10px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Accept</button>'
        + '</div>'
        + '</div>'
        + '</div>';
}

window.addPendingWorkspace = function (data) {
    const container = document.getElementById('workspace-list');
    if (!container) return;
    if (container.querySelector('[data-workspace="' + data.code + '"]')) return;
    container.insertAdjacentHTML('afterbegin', pendingWorkspaceRow(data));
    const empty = document.getElementById('workspace-empty');
    if (empty) empty.classList.add('hidden');
    if (window.recalcWsUnreadTotal) window.recalcWsUnreadTotal();
};

window.refreshWorkspaceMembers = function (code) {
    if (currentWsCode === code) loadWorkspaceMembers(code);
};

window.respondWorkspaceInvite = function (code, accept) {
    fetch('/workspaces/' + encodeURIComponent(code) + '/invite-response', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ accept }),
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (!ok) return;
            replaceWorkspaceList(data.html);
            if (accept) {
                switchTab('workspace');
                const row = data.code ? document.querySelector('[data-workspace="' + data.code + '"]') : null;
                if (row) window.openWorkspace(row, row.dataset.name, data.code, row.dataset.created);
            }
        })
        .catch(function () {});
};

let pendingInvite = null;

window.confirmWorkspaceInvite = function (code, accept) {
    pendingInvite = { code, accept };
    const text = document.getElementById('ws-invite-confirm-text');
    if (text) text.textContent = accept
        ? 'Accept the invitation to join this workspace?'
        : 'Reject this workspace invitation?';
    openModal('ws-invite-confirm-modal');
};

window.confirmWorkspaceInviteAction = function () {
    if (!pendingInvite) return;
    closeModal('ws-invite-confirm-modal');
    const { code, accept } = pendingInvite;
    pendingInvite = null;
    respondWorkspaceInvite(code, accept);
};

window.openAddWorkspaceMemberModal = function () {
    const input = document.getElementById('add-workspace-member-input');
    const error = document.getElementById('add-workspace-member-error');
    if (input) input.value = '';
    if (error) error.classList.add('hidden');
    openModal('add-workspace-member-modal');
};

window.submitAddWorkspaceMember = function () {
    const input = document.getElementById('add-workspace-member-input');
    const error = document.getElementById('add-workspace-member-error');
    if (!input || !currentWsCode) return;
    const identifier = input.value.trim();
    if (!identifier) {
        if (error) {
            error.textContent = 'Enter a username or email.';
            error.classList.remove('hidden');
        }
        return;
    }
    if (error) error.classList.add('hidden');
    fetch('/workspaces/' + encodeURIComponent(currentWsCode) + '/members', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ identifier }),
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (!ok) {
                if (error) {
                    error.textContent = data.message || 'Failed to invite member.';
                    error.classList.remove('hidden');
                }
                return;
            }
            closeModal('add-workspace-member-modal');
            input.value = '';
        })
        .catch(function () {
            if (error) {
                error.textContent = 'Something went wrong. Please try again.';
                error.classList.remove('hidden');
            }
        });
};

const addWorkspaceMemberInput = document.getElementById('add-workspace-member-input');
if (addWorkspaceMemberInput) {
    addWorkspaceMemberInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            window.submitAddWorkspaceMember();
        }
    });
}

window.openWorkspaceBio = function () {
    const text = document.getElementById('rightbar-ws-about');
    const target = document.getElementById('bio-modal-text');
    if (!text || !target || !text.textContent.trim()) return;
    target.textContent = text.textContent;
    openModal('bio-modal');
};

let createWsAvatarFile = null;
let createWsInvites = [];

function onCreateWorkspaceAvatarUpload(blob) {
    createWsAvatarFile = blob;
    const img = document.getElementById('create-ws-avatar');
    if (img) img.classList.remove('hidden');
    const fallback = document.getElementById('create-ws-avatar-fallback');
    if (fallback) fallback.classList.add('hidden');
}

function resetCreateWorkspaceForm() {
    const name = document.getElementById('workspace-name-input');
    const code = document.getElementById('create-ws-code');
    const bio = document.getElementById('create-ws-bio');
    const inviteInput = document.getElementById('create-ws-invite-input');
    if (name) name.value = '';
    if (code) code.value = '';
    if (bio) bio.value = '';
    if (inviteInput) inviteInput.value = '';
    createWsAvatarFile = null;
    createWsInvites = [];
    renderCreateInvites();
    const img = document.getElementById('create-ws-avatar');
    if (img) img.classList.add('hidden');
    const fallback = document.getElementById('create-ws-avatar-fallback');
    if (fallback) fallback.classList.remove('hidden');
    const contactsList = document.getElementById('create-ws-contacts-list');
    if (contactsList) contactsList.classList.add('hidden');
    const contactsToggle = document.getElementById('create-ws-contacts-toggle');
    if (contactsToggle) contactsToggle.textContent = 'Select from contacts';
    renderCreateWsContacts();
}

window.openCreateWorkspaceModal = function () {
    resetCreateWorkspaceForm();
    workspaceError('create-workspace-error', '');
    setAvatarTarget('create-ws-avatar', onCreateWorkspaceAvatarUpload);
    openModal('create-workspace-modal');
};

window.rollCreateWorkspaceCode = function () {
    const input = document.getElementById('create-ws-code');
    if (!input) return;
    const CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += CHARS[Math.floor(Math.random() * CHARS.length)];
    }
    input.value = code;
};

function renderCreateInvites() {
    const list = document.getElementById('create-ws-invites-list');
    if (!list) return;
    list.innerHTML = createWsInvites.map(function (idf, i) {
        return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/5 text-[10px] text-white/70">' + escapeHtml(idf)
            + '<button type="button" onclick="removeCreateWorkspaceInvite(' + i + ')" class="text-white/40 hover:text-white transition-colors cursor-pointer" title="Remove">&times;</button></span>';
    }).join('');
}

window.addCreateWorkspaceInvite = function () {
    const input = document.getElementById('create-ws-invite-input');
    if (!input) return;
    const identifier = input.value.trim();
    if (!identifier || createWsInvites.includes(identifier)) return;
    createWsInvites.push(identifier);
    input.value = '';
    renderCreateInvites();
    renderCreateWsContacts();
    input.focus();
};

window.removeCreateWorkspaceInvite = function (index) {
    createWsInvites.splice(index, 1);
    renderCreateInvites();
    renderCreateWsContacts();
};

let createWsContacts = null;

window.toggleCreateWsContacts = function () {
    const list = document.getElementById('create-ws-contacts-list');
    const toggle = document.getElementById('create-ws-contacts-toggle');
    if (!list) return;
    const opening = list.classList.contains('hidden');
    list.classList.toggle('hidden', !opening);
    if (toggle) toggle.textContent = opening ? 'Hide contacts' : 'Select from contacts';
    if (opening && createWsContacts === null) {
        fetch('/contacts/options')
            .then(function (response) { return response.json(); })
            .then(function (contacts) {
                createWsContacts = contacts || [];
                renderCreateWsContacts();
            })
            .catch(function () { createWsContacts = []; });
    }
    renderCreateWsContacts();
};

function renderCreateWsContacts() {
    const list = document.getElementById('create-ws-contacts-list');
    if (!list || createWsContacts === null) return;
    if (!createWsContacts.length) {
        list.innerHTML = '<p class="text-[10px] text-white/30 text-center py-2">No contacts yet.</p>';
        return;
    }
    list.innerHTML = createWsContacts.map(function (c) {
        const checked = createWsInvites.includes(c.username);
        const avatar = c.has_avatar
            ? '<img src="' + c.avatar + '" alt="" class="w-7 h-7 rounded-full object-cover">'
            : '<div class="w-7 h-7 rounded-full bg-white/8 flex items-center justify-center text-[9px] font-medium text-white/60">' + escapeHtml(c.avatar) + '</div>';
        return '<button type="button" data-username="' + escapeHtml(c.username) + '" onclick="toggleCreateWsContact(this.dataset.username)" class="w-full flex items-center gap-2 px-2 py-1.5 rounded hover:bg-white/5 transition-colors cursor-pointer text-left">'
            + '<span class="shrink-0 w-4 h-4 rounded-sm border flex items-center justify-center ' + (checked ? 'bg-[#E091A9] border-[#E091A9]' : 'border-white/20') + '">'
            + (checked ? '<svg viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="1.8" class="w-2.5 h-2.5 text-[#0A0A0A]"><path stroke-linecap="round" stroke-linejoin="round" d="M1 4.5L4.5 8L11 1"/></svg>' : '')
            + '</span>'
            + avatar
            + '<span class="flex-1 min-w-0"><span class="block text-[11px] font-medium text-white/80 truncate">' + escapeHtml(c.name) + '</span>'
            + '<span class="block text-[10px] text-white/30 truncate">@' + escapeHtml(c.username) + '</span></span>'
            + '</button>';
    }).join('');
}

window.toggleCreateWsContact = function (username) {
    const idx = createWsInvites.indexOf(username);
    if (idx >= 0) {
        createWsInvites.splice(idx, 1);
    } else {
        createWsInvites.push(username);
    }
    renderCreateInvites();
    renderCreateWsContacts();
};

const createWsInviteInput = document.getElementById('create-ws-invite-input');
if (createWsInviteInput) {
    createWsInviteInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            window.addCreateWorkspaceInvite();
        }
    });
}

window.submitCreateWorkspace = function () {
    const input = document.getElementById('workspace-name-input');
    const name = input.value.trim();
    if (!name) {
        workspaceError('create-workspace-error', 'Workspace name is required.');
        return;
    }
    workspaceError('create-workspace-error', '');
    const form = new FormData();
    form.append('name', name);
    const codeInput = document.getElementById('create-ws-code');
    if (codeInput && codeInput.value.trim()) form.append('code', codeInput.value.trim());
    const bio = document.getElementById('create-ws-bio');
    if (bio && bio.value.trim()) form.append('bio', bio.value.trim());
    if (createWsAvatarFile) form.append('avatar', createWsAvatarFile);
    createWsInvites.forEach(function (identifier) {
        form.append('invites[]', identifier);
    });
    fetch('/workspaces', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: form,
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (!ok) {
                workspaceError('create-workspace-error', data.message || 'Failed to create workspace.');
                return;
            }
            closeModal('create-workspace-modal');
            resetCreateWorkspaceForm();
            replaceWorkspaceList(data.html);
            switchTab('workspace');
            const row = data.code ? document.querySelector('[data-workspace="' + data.code + '"]') : null;
            if (row) window.openWorkspace(row, row.dataset.name, data.code, row.dataset.created);
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
            const row = data.code ? document.querySelector('[data-workspace="' + data.code + '"]') : null;
            if (row) window.openWorkspace(row, row.dataset.name, data.code, row.dataset.created);
        })
        .catch(function () {
            workspaceError('join-workspace-error', 'Something went wrong. Please try again.');
        });
};
