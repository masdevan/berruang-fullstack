import './auth.js';
import './chat-layout.js';
import { appendMessage, currentChatName, pushMessage, updateLocalFileUrl } from './chat/bubbles.js';
import { saveDraft, applyDraftPreview, sendDraftSync } from './chat/draft.js';

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
    loadAttachments();
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

        if (pendingByChat[currentChatName] && pendingByChat[currentChatName].length) {
            clearTimeout(typingTimer);
            reportTyping(false);
            pendingByChat[currentChatName].slice().forEach(function (item) {
                sendFile(item);
            });
            pendingByChat[currentChatName] = [];
            renderAttachPreview();
            persistChat(currentChatName);
            persistPendingLabel(currentChatName);
            input.value = '';
            input.style.height = 'auto';
            return;
        }

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

                saveDraft(currentChatName, '');
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
            if (currentChatName) saveDraft(currentChatName, this.value);
        });
    }
});

let pendingByChat = {};
let attachSeq = 0;

let idbPromise = null;
function openDb() {
    if (!idbPromise) {
        idbPromise = new Promise(function (resolve, reject) {
            const req = indexedDB.open('berruang-attachments', 1);
            req.onupgradeneeded = function () {
                if (!req.result.objectStoreNames.contains('chat')) {
                    req.result.createObjectStore('chat', { keyPath: 'username' });
                }
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error); };
        });
    }
    return idbPromise;
}

function persistChat(username) {
    const items = (pendingByChat[username] || []).map(function (f) {
        return { id: f.id, kind: f.kind, file: f.file };
    });
    return openDb().then(function (db) {
        const store = db.transaction('chat', 'readwrite').objectStore('chat');
        return new Promise(function (resolve, reject) {
            const req = items.length ? store.put({ username: username, items: items }) : store.delete(username);
            req.onsuccess = resolve;
            req.onerror = function () { reject(req.error); };
        });
    }).catch(function () {});
}

function persistPendingLabel(username) {
    const label = window.getPendingLabel(username);
    if (label) {
        localStorage.setItem('berruang-pending-label:' + username, label);
    } else {
        localStorage.removeItem('berruang-pending-label:' + username);
    }
    sendDraftSync(username);
    applyDraftPreview(username);
}

function loadAttachments() {
    return openDb().then(function (db) {
        return new Promise(function (resolve, reject) {
            const req = db.transaction('chat').objectStore('chat').getAll();
            req.onsuccess = function () {
                (req.result || []).forEach(function (rec) {
                    pendingByChat[rec.username] = rec.items.map(function (it) {
                        return { id: it.id, kind: it.kind, file: it.file, url: URL.createObjectURL(it.file), failed: false, sent: false };
                    });
                    persistPendingLabel(rec.username);
                });
                resolve();
            };
            req.onerror = reject;
        });
    }).then(function () {
        if (currentChatName) {
            renderAttachPreview();
            applyDraftPreview(currentChatName);
        }
    }).catch(function () {});
}

(function setupAttachDrag() {
    const bar = document.getElementById('attach-preview-bar');
    if (!bar) return;
    bar.addEventListener('dragstart', function (e) { e.preventDefault(); });
    window.__attachDragged = false;
    let dragging = false;
    let startX = 0;
    let startLeft = 0;
    let targetLeft = null;
    let rafId = null;
    bar.addEventListener('pointerdown', function (e) {
        if (e.target.closest('button')) return;
        window.__attachDragged = false;
        dragging = true;
        startX = e.clientX;
        startLeft = bar.scrollLeft;
        if (rafId) cancelAnimationFrame(rafId);
        (function frame() {
            if (!dragging) return;
            if (targetLeft !== null) {
                bar.scrollLeft = targetLeft;
                targetLeft = null;
            }
            rafId = requestAnimationFrame(frame);
        })();
    });
    bar.addEventListener('pointermove', function (e) {
        if (!dragging) return;
        if (Math.abs(e.clientX - startX) > 5) window.__attachDragged = true;
        targetLeft = startLeft - (e.clientX - startX);
    });
    function stop() {
        dragging = false;
        if (rafId) cancelAnimationFrame(rafId);
    }
    bar.addEventListener('pointerup', stop);
    bar.addEventListener('pointercancel', stop);
})();

window.getPendingLabel = function (username) {
    const items = pendingByChat[username] || [];
    if (!items.length) return '';
    if (items.length === 1) {
        const kind = items[0].kind;
        return kind === 'image' ? 'Photo' : kind === 'video' ? 'Video' : items[0].file.name;
    }
    return items.length + ' items';
};

window.triggerAttach = function (kind) {
    const input = document.getElementById('attach-file-input');
    input.accept = kind === 'photo'
        ? 'image/*,video/*'
        : '.pdf,.txt,.zip,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv';
    input.onchange = function () {
        Array.from(input.files).forEach(queueFile);
        input.value = '';
    };
    input.click();
};

function queueFile(file) {
    if (!currentChatName) return;
    const kind = file.type.startsWith('image/') ? 'image' : file.type.startsWith('video/') ? 'video' : 'document';
    if (!pendingByChat[currentChatName]) pendingByChat[currentChatName] = [];
    pendingByChat[currentChatName].push({ id: 'att-' + (++attachSeq), file, kind, url: URL.createObjectURL(file), failed: false });
    renderAttachPreview();
    persistChat(currentChatName);
    persistPendingLabel(currentChatName);
}

window.renderAttachPreview = function () {
    const bar = document.getElementById('attach-preview-bar');
    if (!bar) return;
    const items = (pendingByChat[currentChatName] || []).filter(function (f) { return !f.sent; });
    if (!items.length) {
        bar.classList.add('hidden');
        bar.innerHTML = '';
        return;
    }
    bar.classList.remove('hidden');
    bar.innerHTML = '';
    items.forEach(function (item) {        const el = document.createElement('div');
        el.dataset.attachId = item.id;
        el.className = 'relative shrink-0 w-14 h-14 rounded-lg bg-white/5 border border-white/10 cursor-pointer';
        if (item.kind === 'image') {
            el.innerHTML = '<img src="' + item.url + '" class="w-full h-full object-cover rounded-lg">';
        } else {
            const icon = item.kind === 'video'
                ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg>'
                : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>';
            el.innerHTML = '<div class="w-full h-full flex flex-col items-center justify-center gap-0.5 text-white/60 px-1">' + icon + '<span class="text-[8px] text-white/40 truncate max-w-full">' + item.file.name + '</span></div>';
        }
        el.addEventListener('click', function () {
            if (window.__attachDragged) { window.__attachDragged = false; return; }
            if (item.kind === 'image') window.openMediaModal(item.url);
            if (item.kind === 'video') window.openMediaModal(item.url, 'video');
        });
        const rm = document.createElement('button');
        rm.type = 'button';
        rm.textContent = '×';
        rm.title = 'Remove';
        rm.className = 'absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-[#E091A9] text-[#0A0A0A] text-[10px] leading-none flex items-center justify-center cursor-pointer z-10 hover:bg-[#E8A8BC] transition-colors';
        rm.onclick = function (e) { e.stopPropagation(); removeAttach(item.id); };
        el.appendChild(rm);
        bar.appendChild(el);
    });
    applyDraftPreview(currentChatName);
}

function removeAttach(id) {
    let username = null;
    Object.keys(pendingByChat).forEach(function (u) {
        if (!username && pendingByChat[u].some(function (f) { return f.id === id; })) username = u;
    });
    if (!username) return;
    const items = pendingByChat[username] || [];
    const idx = items.findIndex(function (f) { return f.id === id; });
    if (idx === -1) return;
    URL.revokeObjectURL(items[idx].url);
    items.splice(idx, 1);
    persistChat(username);
    persistPendingLabel(username);
    if (username !== currentChatName) return;
    const el = document.querySelector('[data-attach-id="' + id + '"]');
    if (el) {
        el.animate(
            [{ opacity: 1, transform: 'scale(1)' }, { opacity: 0, transform: 'scale(0.8)' }],
            { duration: 150, easing: 'ease-in' }
        ).onfinish = function () { renderAttachPreview(); };
    } else {
        renderAttachPreview();
    }
}

function sendFile(item) {
    if (!currentChatName || item.sent) return;
    item.sent = true;
    const localUrl = URL.createObjectURL(item.file);
    pushMessage({ id: null, from: 'me', text: item.file.name, time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }), type: item.kind, file: { url: localUrl, name: item.file.name } }, true);

    const form = new FormData();
    form.append('to', currentChatName);
    form.append('file', item.file);

    fetch('/messages', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: form
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            const nodes = document.querySelectorAll('#messages-container [src="' + localUrl + '"], #messages-container [href="' + localUrl + '"]');
            if (ok && data.file) {
                nodes.forEach(function (el) {
                    el.setAttribute(el.tagName === 'A' ? 'href' : 'src', data.file.url);
                });
                updateLocalFileUrl(currentChatName, data.id, data.file.url);
            } else {
                item.failed = true;
                const failMsg = (data && data.message) || 'Gagal mengirim file';
                nodes.forEach(function (el) {
                    el.remove();
                    const row = el.closest('[data-chat]');
                    if (row) {
                        const p = document.createElement('p');
                        p.className = 'text-[10px] text-red-400/80 mt-1';
                        p.textContent = 'Tidak terkirim: ' + failMsg;
                        row.firstElementChild.appendChild(p);
                    }
                });
            }
            removeAttach(item.id);
        })
        .catch(function () {
            item.failed = true;
            removeAttach(item.id);
        });
}
