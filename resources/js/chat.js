import './auth.js';
import './chat-layout.js';
import { appendMessage, currentChatName } from './chat/bubbles.js';

import.meta.glob(['../images/**']);

const loaderBar = document.getElementById('top-loader-bar');
const loader = document.getElementById('top-loader');

function finishLoadingBar() {
    if (!loaderBar) return;
    loaderBar.style.width = '100%';
    setTimeout(function () {
        if (!loader) return;
        loader.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 250, easing: 'ease-out' }).onfinish = function () {
            loader.style.display = 'none';
        };
    }, 200);
}

if (loaderBar) {
    if (document.readyState === 'complete') {
        loader.style.display = 'none';
    } else {
        requestAnimationFrame(function () {
            loaderBar.style.width = '30%';
            setTimeout(function () {
                loaderBar.style.width = '90%';
            }, 600);
        });
        window.addEventListener('load', finishLoadingBar);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');

    if (!form) return;

    let typingTimer = null;

    function reportTyping(typing) {
        if (!currentChatName) return;
        fetch('/typing', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ to: currentChatName, typing: typing })
        }).catch(function () {});
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text || !currentChatName) return;

        clearTimeout(typingTimer);
        reportTyping(false);

        sendBtn.disabled = true;
        sendBtn.classList.add('opacity-50', 'cursor-not-allowed');

        fetch('/messages', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ to: currentChatName, body: text })
        })
            .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
            .then(function ({ ok, data }) {
                sendBtn.disabled = false;
                sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                if (!ok) return;

                appendMessage(text, data.time);
                input.value = '';
                input.style.height = 'auto';
            })
            .catch(function () {
                sendBtn.disabled = false;
                sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
    });

    if (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.requestSubmit();
            }
        });

        input.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            clearTimeout(typingTimer);
            reportTyping(true);
            typingTimer = setTimeout(function () { reportTyping(false); }, 1200);
        });
    }
});
