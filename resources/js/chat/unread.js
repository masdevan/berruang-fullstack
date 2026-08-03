const BADGE_CLASSES = 'unread-badge shrink-0 ml-2 min-w-3.75 h-3.75 rounded-full bg-[#E091A9] text-[#0A0A0A] text-[7px] font-semibold flex items-center justify-center px-1 leading-none';

export function bumpUnread(username) {
    const item = conversationItem(username);
    if (!item) return;

    const badge = item.querySelector('.unread-badge');
    if (badge) {
        badge.textContent = Number(badge.textContent) + 1;
    } else {
        item.querySelector('.conversation-last').insertAdjacentHTML('afterend', badgeHtml(1));
    }
    recalcUnreadTotal();
}

export function clearUnread(username) {
    const item = conversationItem(username);
    if (!item) return;

    const badge = item.querySelector('.unread-badge');
    if (badge) badge.remove();
    recalcUnreadTotal();
}

export function recalcUnreadTotal() {
    const badge = document.getElementById('chat-unread-total');
    if (!badge) return;

    let total = 0;
    document.querySelectorAll('#tab-pane-chat .unread-badge').forEach(function (b) {
        total += Number(b.textContent);
    });
    badge.textContent = total > 99 ? '99+' : total;
    badge.classList.toggle('invisible', total === 0);
}

export function ensureConversationItem(msg) {
    let item = conversationItem(msg.sender_username);
    if (item) return item;

    const container = document.querySelector('#tab-pane-chat .overflow-y-auto');
    if (!container) return null;

    const emptyState = container.querySelector('.empty-state');
    if (emptyState) emptyState.remove();

    const escaped = escapeHtml;
    const avatar = msg.sender_has_avatar
        ? '<img src="' + msg.sender_avatar + '" alt="" class="w-9 h-9 rounded-full object-cover">'
        : '<div class="w-9 h-9 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60">' + msg.sender_avatar + '</div>';

    const html = `
        <div data-conversation="${msg.sender.toLowerCase()} ${msg.body.toLowerCase()}" data-user-id="${msg.sender_user_id}" data-name="${escaped(msg.sender)}" data-avatar="${msg.sender_avatar}" data-full-avatar="${msg.sender_full_avatar || ''}" data-has-avatar="${msg.sender_has_avatar ? '1' : '0'}" data-status="offline" data-about="${escaped(msg.sender_bio)}" data-real-name="${escaped(msg.sender)}" data-username="${msg.sender_username}" data-custom-name="${msg.custom ? escaped(msg.sender) : ''}" onclick="openConversation(this.dataset.name, this.dataset.avatar, this.dataset.status, this.dataset.about, this.dataset.customName, this.dataset.realName, this.dataset.username, this.dataset.hasAvatar === '1')" class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer transition-all duration-150 hover:bg-white/5">
            <div class="relative shrink-0">
                ${avatar}
                <div class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-[#0F0F0F] bg-white/20"></div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <p class="flex items-center gap-1.5 min-w-0 text-xs font-medium truncate text-white/80">
                        <span class="truncate">${msg.custom ? escaped(msg.sender) : '@' + msg.sender_username}</span>
                        ${msg.custom ? '' : '<span class="shrink-0 text-[8px] font-medium text-white/35 bg-white/8 rounded-full px-1.5 py-0.5">unsaved</span>'}
                    </p>
                    <p class="text-[10px] text-white/30 shrink-0 ml-2">${msg.time}</p>
                </div>
                <div class="flex items-center justify-between mt-0.5">
                    <div class="flex items-center gap-1 min-w-0 flex-1">
                        <p class="conversation-last text-[11px] text-white/35 truncate"></p>
                    </div>
                </div>
            </div>
        </div>`;

    container.prepend(html.trim());

    return conversationItem(msg.sender_username);
}

function badgeHtml(count) {
    return '<span class="' + BADGE_CLASSES + '">' + count + '</span>';
}

function conversationItem(username) {
    return document.querySelector('[data-username="' + username + '"]');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
