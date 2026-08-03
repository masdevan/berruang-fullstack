import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { pushMessage, currentChatName, updateConversationPreview, filePreviewLabel, markMessagesRead } from './bubbles.js';
import { bumpUnread, clearUnread, ensureConversationItem } from './unread.js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

const userId = document.body.dataset.userId;
if (userId) {
    echo.private('App.Models.User.' + userId)
        .listen('MessageSent', function (e) {
            if (currentChatName === e.sender_username) {
                clearUnread(e.sender_username);
                pushMessage({ id: e.id, from: 'other', text: e.body, time: e.time, type: e.type || 'text', file: e.file || null }, true);
            } else {
                ensureConversationItem(e);
                updateConversationPreview(e.sender_username, filePreviewLabel({ type: e.type, text: e.body }));
                bumpUnread(e.sender_username);
            }
        })
        .listen('TypingEvent', function (e) {
            setTyping(e.from_username, e.typing);
        })
        .listen('MessageRead', function (e) {
            if (currentChatName === e.reader_username) {
                markMessagesRead(e.message_ids);
            }
        });
}

const ITEM_DOT = { online: 'bg-green-500', idle: 'bg-yellow-400', offline: 'bg-white/20' };
const HEADER_TEXT = { online: 'text-green-400/70', idle: 'text-yellow-400/70', offline: 'text-white/30' };
const HEADER_DOT = { online: 'bg-green-400', idle: 'bg-yellow-400', offline: 'bg-white/20' };
const LABEL = { online: 'Online', idle: 'Idle', offline: 'Offline' };

const typingUsers = {};

export function setTyping(username, typing) {
    if (typing) {
        typingUsers[username] = Date.now();
    } else {
        delete typingUsers[username];
    }
    renderTyping(username);
}

function isTyping(username) {
    const ts = typingUsers[username];
    if (!ts) return false;
    if (Date.now() - ts > 4000) {
        delete typingUsers[username];
        return false;
    }
    return true;
}

function renderTyping(username) {
    const item = document.querySelector('[data-username="' + username + '"]');
    const preview = item && item.querySelector('.conversation-last');
    if (preview) {
        if (typingUsers[username]) {
            if (preview.dataset.originalLast === undefined) preview.dataset.originalLast = preview.textContent;
            preview.textContent = 'typing…';
            preview.classList.add('text-[#E091A9]/80', 'italic');
        } else {
            if (preview.dataset.originalLast !== undefined) {
                preview.textContent = preview.dataset.originalLast;
                delete preview.dataset.originalLast;
            }
            preview.classList.remove('text-[#E091A9]/80', 'italic');
        }
    }
    if (currentChatName === username) {
        const el = document.getElementById('chat-header-status');
        if (el) renderHeaderStatus(el);
    }
}

function renderHeaderStatus(el) {
    const item = document.querySelector('[data-username="' + currentChatName + '"]');
    const status = (item && item.dataset.status) || 'offline';
    el.classList.remove('text-green-400/70', 'text-yellow-400/70', 'text-white/30');
    if (isTyping(currentChatName)) {
        el.classList.add('text-[#E091A9]/80');
        el.innerHTML = '<span class="italic">typing…</span>';
        return;
    }
    el.classList.add(HEADER_TEXT[status] || HEADER_TEXT.offline);
    el.innerHTML = '<span class="w-1.5 h-1.5 rounded-full ' + (HEADER_DOT[status] || HEADER_DOT.offline) + ' inline-block -mt-0.5"></span> ' + (LABEL[status] || LABEL.offline);
}

export function setStatus(username, status) {
    const item = document.querySelector('[data-username="' + username + '"]');
    if (item) {
        item.dataset.status = status;
        const dot = item.querySelector('.online-dot');
        if (dot) {
            dot.classList.remove('bg-green-500', 'bg-yellow-400', 'bg-white/20');
            dot.classList.add(ITEM_DOT[status] || ITEM_DOT.offline);
        }
    }
    if (currentChatName === username) {
        const el = document.getElementById('chat-header-status');
        if (el) renderHeaderStatus(el);
        const dot = document.getElementById('rightbar-online-dot');
        if (dot) {
            dot.classList.remove('bg-green-500', 'bg-yellow-400', 'bg-white/20');
            dot.classList.add(ITEM_DOT[status] || ITEM_DOT.offline);
        }
    }
}

function syncStatuses(usernames, fallback) {
    if (!usernames.length) return;
    fetch('/presence-status?users=' + encodeURIComponent(usernames.join(',')))
        .then(function (r) { return r.json(); })
        .then(function (map) {
            usernames.forEach(function (u) {
                setStatus(u, map[u] || fallback || 'offline');
            });
        })
        .catch(function () {});
}

const presence = echo.join('online');
presence.here(function (members) {
    syncStatuses(members.map(function (m) { return m.username; }), 'online');
});
presence.joining(function (member) {
    syncStatuses([member.username], 'online');
});
presence.leaving(function (member) {
    setStatus(member.username, 'offline');
});
presence.listen('UserStatusChanged', function (e) {
    setStatus(e.username, e.status);
});

setInterval(function () {
    const now = Date.now();
    Object.keys(typingUsers).forEach(function (u) {
        if (now - typingUsers[u] > 4000) {
            delete typingUsers[u];
            renderTyping(u);
        }
    });
}, 2000);
