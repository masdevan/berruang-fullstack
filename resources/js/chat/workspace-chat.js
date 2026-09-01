import { BUBBLE_ME, BUBBLE_OTHER, BUBBLE_MEDIA_ME, BUBBLE_MEDIA_OTHER } from './constants.js';
import { addSharedMedia, resetSharedMedia } from './shared-media.js';
import VIDEO_SVG from '../icons/video.js';
import DOC_SVG from '../icons/doc.js';
import DOC_SVG_LG from '../icons/doc-lg.js';
import CHECK_SENT_SVG from '../icons/check-sent.js';
import CHECK_DONE_SVG from '../icons/check-done.js';

let wsMessages = [];
let wsHasMore = false;
let wsLoading = false;
let wsSeq = 0;
let wsPending = [];
let wsPendingSeq = 0;

window.currentWorkspaceCode = null;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function wsText(msg) {
    return msg.body !== undefined ? msg.body : (msg.text || '');
}

function wsLabel(msg) {
    return msg.type === 'image' ? 'Photo' : msg.type === 'video' ? 'Video' : msg.type === 'document' ? 'Document' : wsText(msg);
}

function normalizeWsMessage(msg) {
    return {
        id: msg.id,
        workspace_code: msg.workspace_code,
        body: wsText(msg),
        time: msg.time,
        type: msg.type || 'text',
        from: msg.from || 'other',
        read: !!msg.read,
        sender_user_id: msg.sender_user_id,
        sender_name: msg.sender_name || '',
        sender_username: msg.sender_username || '',
        sender_avatar: msg.sender_avatar || '',
        sender_has_avatar: !!msg.sender_has_avatar,
        file: msg.file || null,
    };
}

function wsBubbleHtml(msg, senderName) {
    const isMe = msg.from === 'me';
    const isText = !msg.type || msg.type === 'text';
    const isMedia = (msg.type === 'image' || msg.type === 'video' || msg.type === 'document') && msg.file;
    const hasCaption = isMedia && msg.body && msg.body !== msg.file.name;
    const timePad = isMedia ? 'px-3 pb-1.5' + (hasCaption ? '' : ' mt-2') : '';
    const bubbleClass = isMedia
        ? (isMe ? BUBBLE_MEDIA_ME : BUBBLE_MEDIA_OTHER)
        : (isMe ? BUBBLE_ME : BUBBLE_OTHER);
    let content = '';
    if (isText) {
        content = '<p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">' + escapeHtml(msg.body) + '</p>';
    } else if (msg.type === 'image') {
        const sizeAttrs = msg.file.width && msg.file.height ? 'width="' + msg.file.width + '" height="' + msg.file.height + '"' : '';
        const caption = hasCaption ? '<div class="px-3 pt-1"><p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">' + escapeHtml(msg.body) + '</p></div>' : '';
        content = '<img src="' + (msg.file.preview_url || msg.file.url) + '" data-full-src="' + msg.file.url + '" ' + sizeAttrs + ' loading="lazy" decoding="async" class="bubble-file block w-full h-auto cursor-pointer bg-white/5">' + caption;
    } else if (msg.type === 'video') {
        const caption = hasCaption ? '<div class="px-3 pt-1"><p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">' + escapeHtml(msg.body) + '</p></div>' : '';
        content = '<video src="' + msg.file.url + '" controls preload="metadata" class="block w-full h-auto bg-black"></video>' + caption;
    } else {
        const caption = hasCaption ? '<div class="px-3 pt-1"><p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">' + escapeHtml(msg.body) + '</p></div>' : '';
        content = '<a href="' + msg.file.url + '" target="_blank" class="flex items-center gap-2 rounded-lg bg-white/5 hover:bg-white/10 transition-colors px-2.5 py-2 mb-1">' + '<div class="w-7 h-7 rounded bg-white/10 flex items-center justify-center text-white/50 shrink-0">' + DOC_SVG + '</div>' + '<span class="text-[10px] text-white/70 truncate max-w-36">' + escapeHtml(msg.file.name) + '</span></a>' + caption;
    }
    return '<div class="relative ' + bubbleClass + '">'
        + (senderName ? '<p class="text-[10px] font-medium text-[#E091A9]/80 mb-0.5">' + escapeHtml(senderName) + '</p>' : '')
        + content
        + '<p class="text-[9px] text-white/25 flex items-center ' + timePad + '">'
        + (isMe ? '<span class="bubble-checks shrink-0 mr-2 ' + (msg.read ? 'text-[#E091A9]' : 'text-white/25') + '">' + (msg.read ? CHECK_DONE_SVG : CHECK_SENT_SVG) + '</span>' : '')
        + '<span class="ml-auto">' + msg.time + '</span></p></div>';
}

function wsMessageRow(msg, grouped) {
    const isMe = msg.from === 'me';
    const avatar = msg.sender_has_avatar
        ? '<img src="' + msg.sender_avatar + '" alt="" class="w-6 h-6 rounded-full object-cover">'
        : '<div class="w-6 h-6 rounded-full bg-white/8 flex items-center justify-center text-[9px] font-medium text-white/60">' + escapeHtml((msg.sender_name || '?').charAt(0).toUpperCase()) + '</div>';
    if (isMe) {
        return '<div class="flex justify-end mb-2" data-ws-message="' + (msg.id || 'pending') + '">'
            + wsBubbleHtml(msg)
            + '</div>';
    }
    return '<div class="flex justify-start ' + (grouped ? 'mb-1' : 'mb-2') + '" data-ws-message="' + (msg.id || 'pending') + '">'
        + (grouped ? '<div class="mr-2 shrink-0 w-6 h-6"></div>' : '<div class="mr-2 shrink-0 self-start">' + avatar + '</div>')
        + wsBubbleHtml(msg, grouped ? '' : (msg.sender_name || ''))
        + '</div>';
}

function isWsGrouped(prev, msg) {
    return msg.from === 'other' && prev && prev.from === 'other' && prev.sender_user_id === msg.sender_user_id;
}

function renderWsAll() {
    const container = document.getElementById('messages-container');
    if (!container) return;
    let prev = null;
    container.innerHTML = wsMessages.map(function (m) {
        const grouped = isWsGrouped(prev, m);
        prev = m;
        return wsMessageRow(m, grouped);
    }).join('');
    container.scrollTop = container.scrollHeight;
}

function appendWsRow(msg) {
    const container = document.getElementById('messages-container');
    if (!container) return;
    container.insertAdjacentHTML('beforeend', wsMessageRow(msg, isWsGrouped(wsMessages[wsMessages.length - 1], msg)));
    container.scrollTop = container.scrollHeight;
    addSharedMedia(msg, 'ws:' + (msg.workspace_code || window.currentWorkspaceCode));
}

window.loadWorkspaceHistory = function (code) {
    const seq = ++wsSeq;
    wsMessages = [];
    wsHasMore = false;
    const container = document.getElementById('messages-container');
    if (container) container.innerHTML = '';
    resetSharedMedia('ws:' + code);
    wsRestorePending(code);
    fetch('/workspaces/' + encodeURIComponent(code) + '/messages')
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (seq !== wsSeq || window.currentWorkspaceCode !== code) return;
            wsHasMore = !!data.has_more;
            wsMessages = (data.messages || []).map(normalizeWsMessage);
            wsMessages.forEach(function (m) { addSharedMedia(m, 'ws:' + code); });
            renderWsAll();
            markWsRead(code);
            patchWsChecks();
        })
        .catch(function () {});
};

function loadOlderWsMessages() {
    if (wsLoading || !wsHasMore || !window.currentWorkspaceCode) return;
    const first = wsMessages[0];
    if (!first || !first.id) return;
    wsLoading = true;
    fetch('/workspaces/' + encodeURIComponent(window.currentWorkspaceCode) + '/messages?before=' + first.id)
        .then(function (response) { return response.json(); })
        .then(function (data) {
            wsLoading = false;
            if (!data.messages || !data.messages.length) {
                wsHasMore = false;
                return;
            }
            const known = {};
            wsMessages.forEach(function (m) { if (m.id) known[m.id] = true; });
            const fresh = data.messages.map(normalizeWsMessage).filter(function (m) { return !m.id || !known[m.id]; });
            if (!fresh.length) {
                wsHasMore = !!data.has_more;
                return;
            }
            fresh.forEach(function (m) { addSharedMedia(m, 'ws:' + window.currentWorkspaceCode); });
            const firstOld = wsMessages[0];
            wsMessages = fresh.concat(wsMessages);
            wsHasMore = !!data.has_more;
            const container = document.getElementById('messages-container');
            const prevHeight = container.scrollHeight;
            let prev = firstOld;
            container.insertAdjacentHTML('afterbegin', fresh.map(function (m) {
                const grouped = isWsGrouped(prev, m);
                prev = m;
                return wsMessageRow(m, grouped);
            }).join(''));
            container.scrollTop += container.scrollHeight - prevHeight;
        })
        .catch(function () { wsLoading = false; });
}

const wsContainer = document.getElementById('messages-container');
if (wsContainer) {
    wsContainer.addEventListener('scroll', function () {
        if (wsContainer.scrollTop < 60 && window.currentWorkspaceCode) loadOlderWsMessages();
    });
}

function markWsRead(code) {
    fetch('/workspaces/' + encodeURIComponent(code) + '/read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({}),
    }).catch(function () {});
    clearWsBadge(code);
}

function clearWsBadge(code) {
    const row = document.querySelector('[data-workspace="' + code + '"]');
    const badge = row && row.querySelector('.ws-unread');
    if (badge) {
        badge.textContent = '0';
        badge.classList.add('hidden');
    }
    recalcWsUnreadTotal();
}

function updateWsSidebar(code, msg, isOwn) {
    const row = document.querySelector('[data-workspace="' + code + '"]');
    if (!row) return;
    const last = row.querySelector('.ws-last');
    if (last) last.textContent = isOwn ? wsLabel(msg) : (msg.sender_name + ' : ' + wsLabel(msg));
    const time = row.querySelector('.ws-time');
    if (time) time.textContent = msg.time;
    if (!isOwn) {
        const badge = row.querySelector('.ws-unread');
        if (badge) {
            const n = (parseInt(badge.textContent, 10) || 0) + 1;
            badge.textContent = n;
            badge.classList.remove('hidden');
        }
    }
    recalcWsUnreadTotal();
}

function recalcWsUnreadTotal() {
    const badge = document.getElementById('ws-unread-total');
    if (!badge) return;
    let total = 0;
    document.querySelectorAll('#tab-pane-workspace .ws-unread').forEach(function (b) {
        if (b.classList.contains('hidden')) return;
        total += Number(b.textContent) || 0;
    });
    badge.textContent = total > 99 ? '99+' : total;
    badge.classList.toggle('invisible', total === 0);
}
window.recalcWsUnreadTotal = recalcWsUnreadTotal;

function patchWsChecks() {
    const container = document.getElementById('messages-container');
    if (!container || !window.currentWorkspaceCode) return;
    const myId = Number(document.body.dataset.userId);
    const positions = window.wsReadPositions || {};
    wsMessages.forEach(function (m) {
        if (m.from !== 'me' || !m.id) return;
        let allRead = true;
        let hasOthers = false;
        Object.keys(positions).forEach(function (uid) {
            if (Number(uid) === myId) return;
            hasOthers = true;
            if ((positions[uid] || 0) < m.id) allRead = false;
        });
        if (hasOthers && allRead !== m.read) {
            m.read = allRead;
            const checks = container.querySelector('[data-ws-message="' + m.id + '"] .bubble-checks');
            if (checks) {
                checks.className = 'bubble-checks shrink-0 mr-2 ' + (allRead ? 'text-[#E091A9]' : 'text-white/25');
                checks.innerHTML = allRead ? CHECK_DONE_SVG : CHECK_SENT_SVG;
            }
        }
    });
}

window.onWsMemberRead = function (readerUserId, lastReadMessageId, code) {
    if (window.currentWorkspaceCode !== code) return;
    window.wsReadPositions[readerUserId] = lastReadMessageId;
    patchWsChecks();
};

let wsTypingTimer = null;

function wsReportTyping(typing) {
    if (!window.currentWorkspaceCode) return;
    fetch('/workspaces/' + encodeURIComponent(window.currentWorkspaceCode) + '/typing', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ typing: typing }),
    }).catch(function () {});
}

const wsMsgInput = document.getElementById('message-input');
if (wsMsgInput) {
    wsMsgInput.addEventListener('input', function () {
        if (!window.currentWorkspaceCode) return;
        clearTimeout(wsTypingTimer);
        wsReportTyping(true);
        wsTypingTimer = setTimeout(function () { wsReportTyping(false); }, 1200);
    });
}

let wsIdbPromise = null;

function wsOpenDb() {
    if (!wsIdbPromise) {
        wsIdbPromise = new Promise(function (resolve, reject) {
            const req = indexedDB.open('berruang-attachments', 1);
            req.onupgradeneeded = function () {
                if (!req.result.objectStoreNames.contains('chat')) {
                    req.result.createObjectStore('chat', { keyPath: 'username' });
                }
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error); };
        });
    }
    return wsIdbPromise;
}

function wsPersistPending(code) {
    const key = 'ws:' + code;
    const items = wsPending.map(function (f) { return { id: f.id, kind: f.kind, file: f.file }; });
    return wsOpenDb().then(function (db) {
        const store = db.transaction('chat', 'readwrite').objectStore('chat');
        return new Promise(function (resolve, reject) {
            const req = items.length ? store.put({ username: key, items: items }) : store.delete(key);
            req.onsuccess = resolve;
            req.onerror = function () { reject(req.error); };
        });
    }).catch(function () {});
}

function wsRestorePending(code) {
    wsOpenDb().then(function (db) {
        return new Promise(function (resolve, reject) {
            const req = db.transaction('chat').objectStore('chat').get('ws:' + code);
            req.onsuccess = function () {
                const rec = req.result;
                if (rec && rec.items) {
                    wsPending = rec.items.map(function (it) {
                        return { id: it.id, kind: it.kind, file: it.file, url: URL.createObjectURL(it.file) };
                    });
                }
                wsRenderPending();
                resolve();
            };
            req.onerror = reject;
        });
    }).catch(function () {});
}

document.addEventListener('DOMContentLoaded', recalcWsUnreadTotal);

window.appendWsMessage = function (raw) {
    const msg = normalizeWsMessage(raw);
    if (window.currentWorkspaceCode === msg.workspace_code) {
        if (wsMessages.some(function (m) { return m.id === msg.id; })) return;
        wsMessages.push(msg);
        appendWsRow(msg);
        markWsRead(msg.workspace_code);
    } else {
        updateWsSidebar(msg.workspace_code, msg, false);
    }
};

window.leaveWorkspaceChat = function () {
    const code = window.currentWorkspaceCode;
    clearTimeout(wsTypingTimer);
    if (code) wsReportTyping(false);
    window.currentWorkspaceCode = null;
    wsMessages = [];
    wsHasMore = false;
    wsSeq++;
    wsPending = [];
    wsRenderPending();
    if (code) wsPersistPending(code);
};

window.wsQueueFile = function (file) {
    if (!window.currentWorkspaceCode) return;
    const kind = file.type.startsWith('image/') ? 'image' : file.type.startsWith('video/') ? 'video' : 'document';
    wsPending.push({ id: 'watt-' + (++wsPendingSeq), file: file, kind: kind, url: URL.createObjectURL(file) });
    wsRenderPending();
    wsPersistPending(window.currentWorkspaceCode);
};

window.wsRenderPending = function () {
    const bar = document.getElementById('attach-preview-bar');
    if (!bar) return;
    if (!wsPending.length) {
        bar.classList.add('hidden');
        bar.innerHTML = '';
        return;
    }
    bar.classList.remove('hidden');
    bar.classList.add('flex');
    bar.innerHTML = '';
    wsPending.forEach(function (item) {
        const el = document.createElement('div');
        el.className = 'relative shrink-0 w-14 h-14 rounded-lg bg-white/5 border border-white/10 cursor-pointer';
        if (item.kind === 'image') {
            el.innerHTML = '<img src="' + item.url + '" class="w-full h-full object-cover rounded-lg">';
        } else {
            el.innerHTML = '<div class="w-full h-full flex flex-col items-center justify-center gap-0.5 text-white/60 px-1">' + (item.kind === 'video' ? VIDEO_SVG : DOC_SVG_LG) + '<span class="text-[8px] text-white/40 truncate max-w-full">' + item.file.name + '</span></div>';
        }
        const rm = document.createElement('button');
        rm.type = 'button';
        rm.textContent = '×';
        rm.className = 'absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-[#E091A9] text-[#0A0A0A] text-[10px] leading-none flex items-center justify-center cursor-pointer z-10';
        rm.onclick = function () {
            const idx = wsPending.indexOf(item);
            if (idx > -1) {
                URL.revokeObjectURL(item.url);
                wsPending.splice(idx, 1);
            }
            wsRenderPending();
            if (window.currentWorkspaceCode) wsPersistPending(window.currentWorkspaceCode);
        };
        el.appendChild(rm);
        bar.appendChild(el);
    });
};

function wsTimeNow() {
    const d = new Date();
    return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
}

let wsSending = false;

window.wsSubmit = function () {
    const input = document.getElementById('message-input');
    if (!input || !window.currentWorkspaceCode || wsSending) return;
    clearTimeout(wsTypingTimer);
    wsReportTyping(false);
    const text = input.value.trim();
    if (wsPending.length) {
        wsSending = true;
        wsPending.slice().forEach(function (item, i) {
            wsSendFile(item, i === 0 ? text : null);
        });
        wsPending = [];
        wsRenderPending();
        wsPersistPending(window.currentWorkspaceCode);
        input.value = '';
        input.style.height = 'auto';
        setTimeout(function () { wsSending = false; }, 400);
        return;
    }
    if (!text) return;
    wsSending = true;
    input.value = '';
    input.style.height = 'auto';
    const time = wsTimeNow();
    fetch('/workspaces/' + encodeURIComponent(window.currentWorkspaceCode) + '/messages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ body: text }),
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (ok && data.id && !wsMessages.some(function (m) { return m.id === data.id; })) {
                const msg = { id: data.id, workspace_code: window.currentWorkspaceCode, body: text, time: time, type: 'text', from: 'me', read: false };
                wsMessages.push(msg);
                appendWsRow(msg);
                updateWsSidebar(window.currentWorkspaceCode, { type: 'text', body: text, time: time }, true);
            }
            wsSending = false;
        })
        .catch(function () { wsSending = false; });
};

function wsSendFile(item, caption) {
    if (!window.currentWorkspaceCode) return;
    const optimistic = {
        id: null,
        body: caption || item.file.name,
        time: wsTimeNow(),
        type: item.kind,
        from: 'me',
        file: { url: item.url, name: item.file.name },
    };
    wsMessages.push(optimistic);
    appendWsRow(optimistic);
    updateWsSidebar(window.currentWorkspaceCode, { type: item.kind, body: optimistic.body, time: optimistic.time }, true);
    const form = new FormData();
    if (caption) form.append('body', caption);
    form.append('file', item.file);
    fetch('/workspaces/' + encodeURIComponent(window.currentWorkspaceCode) + '/messages', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: form,
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (ok && data.id) {
                optimistic.id = data.id;
                const pendingRow = document.getElementById('messages-container').querySelector('[data-ws-message="pending"]');
                if (pendingRow) pendingRow.setAttribute('data-ws-message', data.id);
                patchWsChecks();
            }
        })
        .catch(function () {});
}