import { BUBBLE_ME, BUBBLE_OTHER, BUBBLE_MEDIA_ME, BUBBLE_MEDIA_OTHER } from './constants.js';
import { applyDraftPreview } from './draft.js';
import { resetSharedMedia, addSharedMedia, renderSharedMedia } from './shared-media.js';
import PENCIL_SVG from '../icons/pencil.js';
import CHECK_SENT_SVG from '../icons/check-sent.js';
import CHECK_DONE_SVG from '../icons/check-done.js';
import DOC_SVG from '../icons/doc.js';

export const EMOJIS = ['👍', '❤️', '😂', '😮', '😢'];

export let currentChatName = null;

const localMessages = {};
const pendingReadIds = new Set();

document.addEventListener('load', function (e) {
    const el = e.target;
    if (el.tagName !== 'IMG' || !el.classList.contains('bubble-file')) return;
    el.classList.add('loaded');
    if (!el.getAttribute('width') && el.naturalWidth) {
        el.setAttribute('width', el.naturalWidth);
        el.setAttribute('height', el.naturalHeight);
    }
}, true);

export function setCurrentChat(name) {
    currentChatName = name;
}

export function clearMessages(chatName) {
    if (localMessages[chatName]) localMessages[chatName] = [];
    resetSharedMedia(chatName);
}

export function messageHtml(msg, chatName, index) {
    return `
        <div class="flex ${msg.from === 'me' ? 'justify-end' : 'justify-start'} mb-2 group/bubble" data-chat="${chatName}" data-index="${index}">
            ${bubbleInnerHtml(msg, chatName, index)}
        </div>`;
}

function bubbleInnerHtml(msg, chatName, index) {
    const isMe = msg.from === 'me';
    const isText = !msg.type || msg.type === 'text';
    const isMedia = (msg.type === 'image' || msg.type === 'video') && msg.file;
    const hasCaption = isMedia && msg.text && msg.text !== msg.file.name;
    const truncated = isText && msg.text.length > 1000;
    const text = truncated ? msg.text.slice(0, 1000) + '…' : msg.text;
    const bubbleClass = isMedia
        ? (isMe ? BUBBLE_MEDIA_ME : BUBBLE_MEDIA_OTHER)
        : (isMe ? BUBBLE_ME : BUBBLE_OTHER);
    const reaction = msg.reaction
        ? `<button type="button" class="bubble-react-toggle cursor-pointer absolute -bottom-2 ${isMe ? 'right-2' : 'left-2'} bg-[#1A1A1A] border border-white/10 rounded-full px-1.5 py-0.5 text-[10px] leading-none hover:scale-110 transition-transform">${msg.reaction}</button>`
        : '';
    const pillBottom = msg.reaction ? '-bottom-9' : '-bottom-5';
    const actions = `
        <div class="bubble-pill absolute ${pillBottom} ${isMe ? 'right-0' : 'left-0'} bg-[#1A1A1A] border border-white/10 rounded-full px-1 py-0.5 flex items-center gap-0.5 z-10">
            ${EMOJIS.map(emoji => `<button type="button" class="bubble-react cursor-pointer w-5 h-5 flex items-center justify-center text-[11px] leading-none hover:scale-125 transition-transform" data-emoji="${emoji}">${emoji}</button>`).join('')}
            ${isMe && isText ? `<button type="button" class="bubble-edit cursor-pointer w-5 h-5 flex items-center justify-center text-white/40 hover:text-white transition-colors" title="Edit">${PENCIL_SVG}</button>` : ''}
        </div>`;
    return `
        <div class="relative ${bubbleClass}">
            ${actions}
            ${mediaHtml(msg, isText)}
            ${truncated ? `<button type="button" class="bubble-expand cursor-pointer mt-1 text-[10px] text-[#E091A9] hover:text-[#E8A8BC] transition-colors">Lihat selengkapnya</button>` : ''}
            <p class="text-[9px] text-white/25 flex items-center ${isMedia ? 'px-3 pb-1.5' + (hasCaption ? '' : ' mt-2') : ''}">
                ${isMe ? checksHtml(msg.status) : ''}
                <span class="ml-auto">${msg.time}</span>
            </p>
            ${reaction}
        </div>`;
}

function checksHtml(status) {
    if (status === 'read') return `<span class="text-[#E091A9] shrink-0 mr-2">${CHECK_DONE_SVG}</span>`;
    if (status === 'delivered') return `<span class="text-white/25 shrink-0 mr-2">${CHECK_DONE_SVG}</span>`;
    return `<span class="text-white/25 shrink-0 mr-2">${CHECK_SENT_SVG}</span>`;
}

function mediaHtml(msg, isText) {
    if (isText) {
        return `<p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">${escapeHtml(msg.text)}</p>`;
    }
    const caption = msg.text && msg.text !== msg.file.name
        ? `<p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">${escapeHtml(msg.text)}</p>`
        : '';
    const captionBlock = caption ? `<div class="px-3 pt-1">${caption}</div>` : '';
    const dims = msg.file.width && msg.file.height;
    const sizeAttrs = dims ? `width="${msg.file.width}" height="${msg.file.height}"` : '';
    if (msg.type === 'image' && msg.file) {
        const preview = msg.file.preview_url || msg.file.url;
        return `<img src="${preview}" alt="${escapeHtml(msg.file.name)}" title="Click to view" data-full-src="${escapeHtml(msg.file.url)}" ${sizeAttrs} loading="lazy" decoding="async" class="bubble-file block w-full h-auto cursor-pointer bg-white/5">${captionBlock}`;
    }
    if (msg.type === 'video' && msg.file) {
        return `<video src="${msg.file.url}" controls ${sizeAttrs} loading="lazy" preload="metadata" class="block w-full h-auto bg-black"></video>${captionBlock}`;
    }
    if (msg.type === 'document' && msg.file) {
        return `
            <a href="${msg.file.url}" target="_blank" class="flex items-center gap-2 rounded-lg bg-white/5 hover:bg-white/10 transition-colors px-2.5 py-2 mb-1">
                <div class="w-7 h-7 rounded bg-white/10 flex items-center justify-center text-white/50 shrink-0">
                    ${DOC_SVG}
                </div>
                <span class="text-[10px] text-white/70 truncate max-w-36">${escapeHtml(msg.file.name)}</span>
            </a>${caption}`;
    }
    return `<p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">${escapeHtml(msg.text)}</p>`;
}

export function filePreviewLabel(msg) {
    return msg.type === 'image' ? 'Photo' : msg.type === 'video' ? 'Video' : msg.type === 'document' ? 'Document' : msg.text;
}

export function updateLocalFileUrl(username, id, url, width, height) {
    const msgs = localMessages[username];
    if (!msgs) return;
    for (let i = msgs.length - 1; i >= 0; i--) {
        if (msgs[i].from === 'me' && msgs[i].file && msgs[i].file.url.startsWith('blob:') && !msgs[i].id) {
            msgs[i].file.url = url;
            msgs[i].id = id;
            if (pendingReadIds.delete(String(id))) {
                msgs[i].status = 'read';
                renderAllMessages();
            }
            if (width && height) {
                msgs[i].file.width = width;
                msgs[i].file.height = height;
                document.querySelectorAll('#messages-container [src="' + url + '"]').forEach(function (el) {
                    el.setAttribute('width', width);
                    el.setAttribute('height', height);
                });
            }
            renderSharedMedia();
            break;
        }
    }
}

export function pushMessage(msg, animate = false) {
    const container = document.getElementById('messages-container');
    if (!container || !currentChatName) return;

    if (!localMessages[currentChatName]) localMessages[currentChatName] = [];
    const messages = localMessages[currentChatName];
    if (msg.id && messages.some(function (m) { return m.id === msg.id; })) return;

    if (msg.from === 'me' && !msg.status) {
        if (msg.read_at || pendingReadIds.has(String(msg.id))) {
            msg.status = 'read';
            if (msg.id) pendingReadIds.delete(String(msg.id));
        } else {
            const item = document.querySelector('[data-username="' + currentChatName + '"]');
            const online = item && (item.dataset.status === 'online' || item.dataset.status === 'idle');
            msg.status = online ? 'delivered' : 'sent';
        }
    }

    messages.push(msg);
    addSharedMedia(msg, currentChatName);
    container.insertAdjacentHTML('beforeend', messageHtml(msg, currentChatName, messages.length - 1));
    if (animate) {
        container.lastElementChild.animate(
            [{ opacity: 0, transform: 'translateY(10px) scale(0.96)' }, { opacity: 1, transform: 'translateY(0) scale(1)' }],
            { duration: 180, easing: 'ease-out' }
        );
    }
    container.scrollTop = container.scrollHeight;
    updateConversationPreview(currentChatName, filePreviewLabel(msg), msg.from === 'me' ? msg.status : null);
}

export function markMessagesRead(messageIds) {
    const msgs = localMessages[currentChatName];
    let changed = false;
    messageIds.forEach(function (id) {
        const key = String(id);
        const target = msgs && msgs.find(function (m) {
            return m.from === 'me' && String(m.id) === key;
        });
        if (target) {
            pendingReadIds.delete(id);
            if (target.status !== 'read') {
                target.status = 'read';
                changed = true;
            }
        } else {
            pendingReadIds.add(key);
        }
    });
    if (!changed) return;
    renderAllMessages();
}

function renderAllMessages() {
    const container = document.getElementById('messages-container');
    const msgs = localMessages[currentChatName];
    if (!container || !msgs) return;
    const scroll = container.scrollTop;
    container.innerHTML = msgs.map(function (m, i) {
        return messageHtml(m, currentChatName, i);
    }).join('');
    container.scrollTop = scroll;
}

export function updateConversationPreview(username, text, status) {
    const item = document.querySelector('[data-username="' + username + '"]');
    const preview = item && item.querySelector('.conversation-last');
    if (preview) {
        preview.textContent = text;
        delete preview.dataset.draftOriginal;
    }
    if (item) item.dataset.previewOwner = status ? 'me' : 'other';
    updatePreviewCheck(item, status);
    applyDraftPreview(username);
}

function updatePreviewCheck(item, status) {
    if (!item) return;
    const preview = item.querySelector('.conversation-last');
    let check = item.querySelector('.conversation-check');
    if (!status) {
        if (check) check.remove();
        return;
    }
    if (!check && preview) {
        check = document.createElement('span');
        check.className = 'conversation-check shrink-0 text-white/35';
        preview.parentElement.insertBefore(check, preview);
    }
    if (!check) return;
    check.dataset.check = status;
    check.classList.toggle('text-[#E091A9]', status === 'read');
    check.classList.toggle('text-white/35', status !== 'read');
    check.innerHTML = status === 'read' || status === 'delivered' ? CHECK_DONE_SVG : CHECK_SENT_SVG;
}

export function markPreviewRead(username) {
    const item = document.querySelector('[data-username="' + username + '"]');
    if (!item || item.dataset.previewOwner !== 'me') return;
    updatePreviewCheck(item, 'read');
}

function bubbleRow(target) {
    return target.closest('[data-chat]');
}

function messageFor(row) {
    const chatName = row.dataset.chat;
    const index = Number(row.dataset.index);
    const messages = localMessages[chatName];
    return messages ? { chatName, index, message: messages[index] } : null;
}

function rerender() {
    renderAllMessages();
}

function enterEdit(row) {
    const { message } = messageFor(row);
    const bubble = row.firstElementChild;
    const width = bubble.offsetWidth;
    bubble.innerHTML = `
        <div class="bubble-edit-mode">
            <textarea class="bubble-edit-input w-full bg-white/5 rounded-sm px-3 py-1 text-xs text-white outline-none focus:border focus:border-[#E091A9]/50 resize-none leading-relaxed" rows="1">${escapeHtml(message.text)}</textarea>
            <div class="flex justify-end gap-1">
                <button type="button" class="bubble-cancel cursor-pointer text-[10px] px-2 py-1 rounded-sm bg-white/5 text-white/50 hover:text-white hover:bg-white/10 transition-colors">Cancel</button>
                <button type="button" class="bubble-save cursor-pointer text-[10px] px-2 py-1 rounded-sm bg-[#E091A9] text-[#0A0A0A] font-medium hover:bg-[#E8A8BC] transition-colors">Save</button>
            </div>
        </div>`;
    bubble.style.width = width + 'px';
    const textarea = bubble.querySelector('textarea');
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
    textarea.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
    textarea.focus();
}

function saveEdit(row) {
    const { chatName, message } = messageFor(row);
    const input = row.querySelector('.bubble-edit-input');
    const text = input.value.trim();
    if (!text) return;

    message.text = text;
    rerender();
    updateConversationPreview(chatName, text);
}

function toggleReaction(row, emoji) {
    const { message } = messageFor(row);
    message.reaction = message.reaction === emoji ? null : emoji;
    rerender();
}

function hideAllPills() {
    document.querySelectorAll('.bubble-pill.pill-visible').forEach(function (pill) {
        pill.classList.remove('pill-visible');
    });
}

document.addEventListener('contextmenu', function (e) {
    const row = e.target.closest('[data-chat]');
    if (!row) return;

    e.preventDefault();
    const pill = row.querySelector('.bubble-pill');
    if (!pill) return;

    hideAllPills();
    if (!pill.classList.contains('pill-visible')) {
        pill.classList.add('pill-visible');
    }
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('.bubble-pill')) {
        hideAllPills();
    }

    const reactToggle = e.target.closest('.bubble-react-toggle');
    if (reactToggle) {
        const row = bubbleRow(reactToggle);
        if (row) {
            const { message } = messageFor(row);
            message.reaction = null;
            rerender();
        }
        return;
    }

    const fileImg = e.target.closest('img.bubble-file');
    if (fileImg) {
        window.openMediaModal(fileImg.dataset.fullSrc || fileImg.src);
        return;
    }

    const reactBtn = e.target.closest('.bubble-react');
    if (reactBtn) {
        const row = bubbleRow(reactBtn);
        if (row) toggleReaction(row, reactBtn.dataset.emoji);
        return;
    }

    const editBtn = e.target.closest('.bubble-edit');
    if (editBtn) {
        const row = bubbleRow(editBtn);
        if (row) enterEdit(row);
        return;
    }

    const saveBtn = e.target.closest('.bubble-save');
    if (saveBtn) {
        const row = bubbleRow(saveBtn);
        if (row) saveEdit(row);
        return;
    }

    const expandBtn = e.target.closest('.bubble-expand');
    if (expandBtn) {
        const row = bubbleRow(expandBtn);
        if (row) {
            const { message } = messageFor(row);
            row.querySelector('.bubble-text').textContent = message.text;
            expandBtn.remove();
        }
        return;
    }

    const cancelBtn = e.target.closest('.bubble-cancel');
    if (cancelBtn) {
        const row = bubbleRow(cancelBtn);
        if (row) rerender();
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
