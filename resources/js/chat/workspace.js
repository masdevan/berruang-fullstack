function workspaceError(id, message) {
    const el = document.getElementById(id);
    if (!el) return;
    if (message) {
        el.textContent = message;
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

function replaceWorkspaceList(html) {
    const container = document.getElementById('workspace-list');
    if (container) container.innerHTML = html;
    const empty = document.getElementById('workspace-empty');
    if (empty) empty.classList.toggle('hidden', html.trim().length > 0);
}

window.submitCreateWorkspace = function () {
    const input = document.getElementById('workspace-name-input');
    const name = input.value.trim();
    if (!name) {
        workspaceError('create-workspace-error', 'Workspace name is required.');
        return;
    }
    workspaceError('create-workspace-error', '');
    fetch('/workspaces', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ name }),
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (!ok) {
                workspaceError('create-workspace-error', data.message || 'Failed to create workspace.');
                return;
            }
            closeModal('create-workspace-modal');
            input.value = '';
            replaceWorkspaceList(data.html);
            switchTab('workspace');
        })
        .catch(function () {
            workspaceError('create-workspace-error', 'Something went wrong. Please try again.');
        });
};

window.submitJoinWorkspace = function () {
    const input = document.getElementById('workspace-code-input');
    const code = input.value.trim();
    if (code.length !== 8) {
        workspaceError('join-workspace-error', 'Enter the 8-character workspace code.');
        return;
    }
    workspaceError('join-workspace-error', '');
    fetch('/workspaces/join', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ code }),
    })
        .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
        .then(function ({ ok, data }) {
            if (!ok) {
                workspaceError('join-workspace-error', data.message || 'Failed to join workspace.');
                return;
            }
            closeModal('join-workspace-modal');
            input.value = '';
            replaceWorkspaceList(data.html);
            switchTab('workspace');
        })
        .catch(function () {
            workspaceError('join-workspace-error', 'Something went wrong. Please try again.');
        });
};
