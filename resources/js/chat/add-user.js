import { openModal, closeModal } from './modal.js';

let addUserPending = false;
let addUserNamesPending = false;
let pendingAdd = null;
let checkTimer = null;
let addUserErrorTimer = null;

function prependContact(html) {
    const list = document.querySelector('#tab-pane-chat .flex-1.overflow-y-auto');
    list.querySelector('.empty-state')?.remove();
    list.insertAdjacentHTML('afterbegin', html);
    const added = list.firstElementChild;
    if (added) {
        added.animate(
            [{ opacity: 0, transform: 'translateY(-8px)' }, { opacity: 1, transform: 'translateY(0)' }],
            { duration: 250, easing: 'ease-out' }
        );
        if (added.dataset.name) added.click();
    }
}

function showAddUserError(message) {
    const error = document.getElementById('add-user-error');
    error.textContent = message;
    error.classList.remove('hidden');
    clearTimeout(addUserErrorTimer);
    addUserErrorTimer = setTimeout(function () {
        error.classList.add('hidden');
    }, 3000);
}

function setTopLoader(active) {
    const loader = document.getElementById('top-loader');
    const bar = document.getElementById('top-loader-bar');
    if (!loader || !bar) return;

    if (active) {
        loader.style.display = '';
        bar.style.width = '30%';
        requestAnimationFrame(function () { bar.style.width = '90%'; });
    } else {
        bar.style.width = '100%';
        setTimeout(function () {
            loader.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 250, easing: 'ease-out' }).onfinish = function () {
                loader.style.display = 'none';
            };
        }, 200);
    }
}

const STATUS_SPINNER = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
const STATUS_CHECK = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/></svg>';
const STATUS_X = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

function setAddUserStatus(html, className) {
    const status = document.getElementById('add-user-status');
    status.innerHTML = html;
    status.className = 'absolute right-2.5 top-1/2 -translate-y-1/2 ' + className;
}

function checkUsernameLive() {
    const input = document.getElementById('add-user-input');
    const status = document.getElementById('add-user-status');
    const username = input.value.trim();
    clearTimeout(checkTimer);

    if (!username) {
        status.classList.add('hidden');
        return;
    }

    setAddUserStatus(STATUS_SPINNER, 'text-white/25');
    status.classList.remove('hidden');

    checkTimer = setTimeout(function () {
        fetch('/check-username/' + encodeURIComponent(username))
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (document.getElementById('add-user-input').value.trim() !== username) return;
                setAddUserStatus(data.taken ? STATUS_CHECK : STATUS_X, data.taken ? 'text-green-400' : 'text-red-400');
            })
            .catch(function () { status.classList.add('hidden'); });
    }, 300);
}

window.submitAddUser = function () {
    const input = document.getElementById('add-user-input');
    const username = input.value.trim();
    if (!username || addUserPending) return;
    addUserPending = true;
    setTopLoader(true);

    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch('/contacts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify({ username: username })
    })
        .then(function (response) {
            return response.json().then(function (data) { return { ok: response.ok, data: data }; });
        })
        .then(function ({ ok, data }) {
            setTopLoader(false);
            if (!ok) {
                showAddUserError(data.message || 'Failed to add user.');
                addUserPending = false;
                return;
            }
            document.getElementById('add-user-error').classList.add('hidden');
            input.value = '';
            addUserPending = false;
            pendingAdd = { id: data.id, html: data.html };
            closeModal('add-user-modal');
            openModal('add-user-names-modal');
        })
        .catch(function () {
            setTopLoader(false);
            showAddUserError('Failed to add user.');
            addUserPending = false;
        });
};

window.submitAddUserNames = function (skip) {
    if (!pendingAdd || addUserNamesPending) return;

    if (skip) {
        const html = pendingAdd.html;
        pendingAdd = null;
        prependContact(html);
        closeModal('add-user-names-modal');
        return;
    }

    const first = document.getElementById('add-user-first-name').value.trim();
    const last = document.getElementById('add-user-last-name').value.trim();
    const error = document.getElementById('add-user-names-error');
    if (!first && !last) {
        error.textContent = 'Fill at least one name or press Skip.';
        error.classList.remove('hidden');
        return;
    }

    addUserNamesPending = true;
    setTopLoader(true);
    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch('/contacts/' + pendingAdd.id, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify({ first_name: first, last_name: last })
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            setTopLoader(false);
            addUserNamesPending = false;
            if (!ok) {
                error.textContent = data.message || 'Failed to save name.';
                error.classList.remove('hidden');
                return;
            }
            pendingAdd = null;
            prependContact(data.html);
            closeModal('add-user-names-modal');
        })
        .catch(function () {
            setTopLoader(false);
            addUserNamesPending = false;
            error.textContent = 'Failed to save name.';
            error.classList.remove('hidden');
        });
};

document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const modal = document.getElementById('add-user-modal');
    if (!modal || modal.classList.contains('hidden')) return;
    if (document.activeElement === document.getElementById('add-user-input')) {
        submitAddUser();
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const modal = document.getElementById('add-user-names-modal');
    if (!modal || modal.classList.contains('hidden')) return;
    if (document.activeElement === document.getElementById('add-user-first-name') ||
        document.activeElement === document.getElementById('add-user-last-name')) {
        submitAddUserNames();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('add-user-input');
    if (input) input.addEventListener('input', checkUsernameLive);
});
