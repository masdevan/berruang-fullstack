import './auth.js';
import './chat-layout.js';

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
        bubble.className = 'flex justify-end mb-2';
        bubble.innerHTML = `
            <div class="max-w-[70%] bg-[#E091A9]/10 rounded-sm px-3 py-1.5">
                <p class="text-xs text-white/85 leading-relaxed">${escapeHtml(text)}</p>
                <p class="text-[9px] text-white/25 text-right mt-0.5">${now()}</p>
            </div>
        `;
        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;

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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function now() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
