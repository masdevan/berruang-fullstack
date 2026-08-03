import { BUBBLE_ME, BUBBLE_OTHER, BUBBLE_MEDIA_ME, BUBBLE_MEDIA_OTHER } from './constants.js';
import { applyDraftPreview } from './draft.js';
import { resetSharedMedia, addSharedMedia } from './shared-media.js';

export const EMOJIS = ['👍', '❤️', '😂', '😮', '😢'];

const PENCIL_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>';

export let currentChatName = null;

const localMessages = {};

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
            <p class="text-[9px] text-white/25 text-right ${isMedia ? 'px-3 pt-1' : ''} mt-0.5">${msg.time}</p>
            ${reaction}
        </div>`;
}

function mediaHtml(msg, isText) {
    if (isText) {
        return `<p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">${escapeHtml(msg.text)}</p>`;
    }
    const caption = msg.text && msg.text !== msg.file.name
        ? `<p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">${escapeHtml(msg.text)}</p>`
        : '';
    const captionBlock = caption ? `<div class="px-3 pt-1.5">${caption}</div>` : '';
    if (msg.type === 'image' && msg.file) {
        return `<img src="${msg.file.url}" alt="${escapeHtml(msg.file.name)}" title="Click to view" class="bubble-file block w-full max-h-72 object-cover cursor-pointer">${captionBlock}`;
    }
    if (msg.type === 'video' && msg.file) {
        return `<video src="${msg.file.url}" controls class="block w-full max-h-72 bg-black"></video>${captionBlock}`;
    }
    if (msg.type === 'document' && msg.file) {
        return `
            <a href="${msg.file.url}" target="_blank" class="flex items-center gap-2 rounded-lg bg-white/5 hover:bg-white/10 transition-colors px-2.5 py-2 mb-1">
                <div class="w-7 h-7 rounded bg-white/10 flex items-center justify-center text-white/50 shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <span class="text-[10px] text-white/70 truncate max-w-36">${escapeHtml(msg.file.name)}</span>
            </a>${caption}`;
    }
    return `<p class="bubble-text text-xs text-white/85 leading-relaxed wrap-break-word">${escapeHtml(msg.text)}</p>`;
}

export function appendMessage(text, time) {
    pushMessage({ id: null, from: 'me', text, time }, true);
}

export function filePreviewLabel(msg) {
    return msg.type === 'image' ? 'Photo' : msg.type === 'video' ? 'Video' : msg.type === 'document' ? 'Document' : msg.text;
}

export function updateLocalFileUrl(username, id, url) {
    const msgs = localMessages[username];
    if (!msgs) return;
    for (let i = msgs.length - 1; i >= 0; i--) {
        if (msgs[i].from === 'me' && msgs[i].file && msgs[i].file.url.startsWith('blob:') && !msgs[i].id) {
            msgs[i].file.url = url;
            msgs[i].id = id;
            addSharedMedia(msgs[i], username);
            break;
        }
    }
}

export function pushMessage(msg, animate = false) {
    const container = document.getElementById('messages-container');
    if (!container || !currentChatName) return;

    if (!localMessages[currentChatName]) localMessages[currentChatName] = [];
    const messages = localMessages[currentChatName];
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
    updateConversationPreview(currentChatName, filePreviewLabel(msg));
}

export function updateConversationPreview(username, text) {
    const item = document.querySelector('[data-username="' + username + '"]');
    const preview = item && item.querySelector('.conversation-last');
    if (preview) {
        preview.textContent = text;
        delete preview.dataset.draftOriginal;
    }
    applyDraftPreview(username);
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

function rerender(row) {
    const { chatName, index, message } = messageFor(row);
    row.innerHTML = bubbleInnerHtml(message, chatName, index);
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
    rerender(row);
    updateConversationPreview(chatName, text);
}

function toggleReaction(row, emoji) {
    const { chatName, index, message } = messageFor(row);
    message.reaction = message.reaction === emoji ? null : emoji;
    rerender(row);
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
            rerender(row);
        }
        return;
    }

    const fileImg = e.target.closest('img.bubble-file');
    if (fileImg) {
        window.openMediaModal(fileImg.src);
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
        if (row) rerender(row);
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
