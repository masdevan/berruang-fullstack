import { pushMessage, clearMessages, currentChatName } from './bubbles.js';

let openSeq = 0;
let historyLoading = false;

export function openChat(username) {
    if (!username) return;
    const seq = ++openSeq;
    historyLoading = true;
    clearMessages(username);

    fetch('/messages/thread?with=' + encodeURIComponent(username))
        .then(function (response) { return response.json(); })
        .then(function (data) {
            historyLoading = false;
            if (seq !== openSeq || currentChatName !== username) return;
            data.messages.forEach(function (msg) {
                pushMessage(normalize(msg));
            });
            const rows = document.querySelectorAll('#messages-container [data-chat="' + username + '"]');
            rows.forEach(function (row, i) {
                row.animate(
                    [{ opacity: 0, transform: 'translateY(10px) scale(0.96)' }, { opacity: 1, transform: 'translateY(0) scale(1)' }],
                    { duration: 250, delay: i * 15, easing: 'ease-out', fill: 'both' }
                );
            });
        })
        .catch(function () {
            historyLoading = false;
        });
}

function normalize(msg) {
    return {
        id: msg.id,
        from: msg.from,
        text: msg.body,
        time: msg.time,
    };
}
