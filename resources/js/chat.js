import './auth.js';
import './chat-layout.js';
import { appendMessage } from './chat/bubbles.js';

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

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        sendBtn.disabled = true;
        sendBtn.classList.add('opacity-50', 'cursor-not-allowed');

        appendMessage(text, now());

        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = false;
        sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
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
        });
    }
});

function now() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
