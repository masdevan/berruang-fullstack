import { BUBBLE_ME, BUBBLE_OTHER } from './constants.js';
import { DEMO_CONVERSATIONS } from './demo-data.js';

export const EMOJIS = ['👍', '❤️', '😂', '😮', '😢'];

const PENCIL_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>';

export let currentChatName = null;

export function setCurrentChat(name) {
    currentChatName = name;
}

export function messageHtml(msg, chatName, index) {
    return `
        <div class="flex ${msg.from === 'me' ? 'justify-end' : 'justify-start'} mb-2 group/bubble" data-chat="${chatName}" data-index="${index}">
            ${bubbleInnerHtml(msg, chatName, index)}
        </div>`;
}

function bubbleInnerHtml(msg, chatName, index) {
    const isMe = msg.from === 'me';
    const truncated = msg.text.length > 1000;
    const text = truncated ? msg.text.slice(0, 1000) + '…' : msg.text;
    const reaction = msg.reaction
        ? `<button type="button" class="bubble-react-toggle cursor-pointer absolute -bottom-2 ${isMe ? 'right-2' : 'left-2'} bg-[#1A1A1A] border border-white/10 rounded-full px-1.5 py-0.5 text-[10px] leading-none hover:scale-110 transition-transform">${msg.reaction}</button>`
        : '';
    const pillBottom = msg.reaction ? '-bottom-9' : '-bottom-5';
    const actions = `
        <div class="bubble-pill absolute ${pillBottom} ${isMe ? 'right-0' : 'left-0'} bg-[#1A1A1A] border border-white/10 rounded-full px-1 py-0.5 flex items-center gap-0.5 z-10">
            ${EMOJIS.map(emoji => `<button type="button" class="bubble-react cursor-pointer w-5 h-5 flex items-center justify-center text-[11px] leading-none hover:scale-125 transition-transform" data-emoji="${emoji}">${emoji}</button>`).join('')}
            ${isMe ? `<button type="button" class="bubble-edit cursor-pointer w-5 h-5 flex items-center justify-center text-white/40 hover:text-white transition-colors" title="Edit">${PENCIL_SVG}</button>` : ''}
        </div>`;
    return `
        <div class="relative ${isMe ? BUBBLE_ME : BUBBLE_OTHER}">
            ${actions}
            <p class="bubble-text text-xs text-white/85 leading-relaxed break-words">${escapeHtml(text)}</p>
            ${truncated ? `<button type="button" class="bubble-expand cursor-pointer mt-1 text-[10px] text-[#E091A9] hover:text-[#E8A8BC] transition-colors">Lihat selengkapnya</button>` : ''}
            <p class="text-[9px] text-white/25 text-right mt-0.5">${msg.time}</p>
            ${reaction}
        </div>`;
}

export function appendMessage(text, time) {
    const container = document.getElementById('messages-container');
    if (!container || !currentChatName || !DEMO_CONVERSATIONS[currentChatName]) return;

    const messages = DEMO_CONVERSATIONS[currentChatName].messages;
    messages.push({ from: 'me', text, time });
    container.insertAdjacentHTML('beforeend', messageHtml(messages[messages.length - 1], currentChatName, messages.length - 1));
    container.scrollTop = container.scrollHeight;
    updateConversationPreview(currentChatName, text);
}

export function updateConversationPreview(name, text) {
    const item = document.querySelector('[data-name="' + name + '"]');
    const preview = item && item.querySelector('.conversation-last');
    if (preview) preview.textContent = text;
}

function bubbleRow(target) {
    return target.closest('[data-chat]');
}

function messageFor(row) {
    const chatName = row.dataset.chat;
    const index = Number(row.dataset.index);
    const messages = DEMO_CONVERSATIONS[chatName] && DEMO_CONVERSATIONS[chatName].messages;
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
            <textarea class="bubble-edit-input w-full bg-white/5 rounded-sm px-2 py-1 text-xs text-white outline-none focus:border focus:border-[#E091A9]/50 resize-none" rows="2">${escapeHtml(message.text)}</textarea>
            <div class="flex justify-end gap-1 mt-1.5">
                <button type="button" class="bubble-cancel cursor-pointer text-[10px] px-2 py-1 rounded-sm bg-white/5 text-white/50 hover:text-white hover:bg-white/10 transition-colors">Cancel</button>
                <button type="button" class="bubble-save cursor-pointer text-[10px] px-2 py-1 rounded-sm bg-[#E091A9] text-[#0A0A0A] font-medium hover:bg-[#E8A8BC] transition-colors">Save</button>
            </div>
        </div>`;
    bubble.style.width = width + 'px';
    bubble.querySelector('textarea').focus();
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
