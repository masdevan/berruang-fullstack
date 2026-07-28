import './auth.js';

import.meta.glob(['../images/**']);

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const container = document.getElementById('messages-container');
    const sendBtn = document.getElementById('send-btn');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        sendBtn.disabled = true;
        sendBtn.classList.add('opacity-50', 'cursor-not-allowed');

        const bubble = document.createElement('div');
        bubble.className = 'flex justify-end mb-3';
        bubble.innerHTML = `
            <div class="max-w-[70%] bg-[#E091A9]/15 border border-[#E091A9]/20 rounded-2xl px-4 py-2.5">
                <p class="text-sm text-white/90 leading-relaxed">${escapeHtml(text)}</p>
                <p class="text-[10px] text-white/30 text-right mt-1">${now()}</p>
            </div>
        `;
        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;

        input.value = '';
        sendBtn.disabled = false;
        sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function now() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
