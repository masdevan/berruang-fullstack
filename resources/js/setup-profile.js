import { initAvatarPicker } from './avatar-picker.js';

const DRAFT_KEY = 'setup-profile-draft';

function readDraft() {
    try {
        return JSON.parse(sessionStorage.getItem(DRAFT_KEY)) || {};
    } catch {
        return {};
    }
}

function saveDraft(patch) {
    try {
        sessionStorage.setItem(DRAFT_KEY, JSON.stringify({ ...readDraft(), ...patch }));
    } catch {}
}

function blobToDataUrl(blob) {
    return new Promise(function (resolve) {
        const reader = new FileReader();
        reader.onload = function () { resolve(reader.result); };
        reader.readAsDataURL(blob);
    });
}

function dataUrlToFile(dataUrl) {
    const match = dataUrl.match(/^data:(.*?);base64,(.*)$/);
    if (!match) return null;

    const bytes = atob(match[2]);
    const buffer = new Uint8Array(bytes.length);
    for (let i = 0; i < bytes.length; i++) {
        buffer[i] = bytes.charCodeAt(i);
    }

    return new File([buffer], 'avatar.jpg', { type: match[1] });
}

document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('setup-avatar-input');
    const avatarPreview = document.getElementById('setup-avatar-preview');
    const bio = document.getElementById('setup-bio');
    const bioCount = document.getElementById('setup-bio-count');
    const continueBtn = document.getElementById('setup-continue-btn');

    const draft = readDraft();
    const hasAvatar = document.getElementById('setup-profile-form').dataset.hasAvatar === '1';

    if (draft.avatar) {
        const file = dataUrlToFile(draft.avatar);
        if (file) {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            avatarInput.files = transfer.files;
            avatarPreview.src = draft.avatar;
        }
    }

    if (draft.bio) {
        bio.value = draft.bio;
    }

    function updateBioCount() {
        bioCount.textContent = bio.value.length + '/500';
        saveDraft({ bio: bio.value });
        updateContinue();
    }

    function updateContinue() {
        continueBtn.disabled = !(bio.value.trim() && (avatarInput.files.length || hasAvatar));
    }

    bio.addEventListener('input', function () {
        this.value = this.value.replace(/[\p{Cc}\p{Zl}\p{Zp}]/gu, '');
        updateBioCount();
    });

    initAvatarPicker({
        previewId: 'setup-avatar-preview',
        onUpload: async function (blob) {
            saveDraft({ avatar: await blobToDataUrl(blob) });

            const transfer = new DataTransfer();
            transfer.items.add(new File([blob], 'avatar.jpg', { type: 'image/jpeg' }));
            avatarInput.files = transfer.files;
            updateContinue();
        },
    });

    updateContinue();
    updateBioCount();
});
