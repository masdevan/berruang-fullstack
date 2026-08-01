import { fadeIn, initAvatarPicker, setModalVisible } from './avatar-picker.js';

document.addEventListener('DOMContentLoaded', function () {
    const accountForm = document.getElementById('account-form');
    const avatarInput = document.getElementById('profile-avatar-input');
    const avatarPreview = document.getElementById('avatar-preview');
    const usernameConfirm = document.getElementById('username-confirm');
    const logoutForm = document.getElementById('logout-form');
    const logoutConfirm = document.getElementById('logout-confirm');

    let usernameConfirmed = false;
    let logoutConfirmed = false;

    initAvatarPicker({
        onUpload: function (blob) {
            avatarPreview.src = URL.createObjectURL(blob);

            const transfer = new DataTransfer();
            transfer.items.add(new File([blob], 'avatar.jpg', { type: 'image/jpeg' }));
            avatarInput.files = transfer.files;
        },
    });

    window.showUsernameConfirm = function () {
        setModalVisible(usernameConfirm, true);
        fadeIn(usernameConfirm);
    };

    window.hideUsernameConfirm = function () {
        setModalVisible(usernameConfirm, false);
    };

    window.confirmUsernameChange = function () {
        usernameConfirmed = true;
        hideUsernameConfirm();
        accountForm.requestSubmit();
    };

    window.showLogoutConfirm = function () {
        setModalVisible(logoutConfirm, true);
        fadeIn(logoutConfirm);
    };

    window.hideLogoutConfirm = function () {
        setModalVisible(logoutConfirm, false);
    };

    window.confirmLogout = function () {
        logoutConfirmed = true;
        hideLogoutConfirm();
        logoutForm.requestSubmit();
    };

    accountForm.addEventListener('submit', function (e) {
        if (usernameConfirmed) return;
        const username = accountForm.querySelector('[name="username"]').value.trim();
        if (username !== accountForm.dataset.originalUsername) {
            e.preventDefault();
            showUsernameConfirm();
        }
    });

    logoutForm.addEventListener('submit', function (e) {
        if (logoutConfirmed) return;
        e.preventDefault();
        showLogoutConfirm();
    });

    const statusEl = document.getElementById('account-status');
    if (statusEl) {
        document.getElementById('account-status-bar').animate(
            [{ width: '100%' }, { width: '0%' }],
            { duration: 3000, easing: 'linear' }
        );
        setTimeout(function () {
            statusEl.animate(
                [{ opacity: 1, transform: 'translateY(0)' }, { opacity: 0, transform: 'translateY(-4px)' }],
                { duration: 250, easing: 'ease-in' }
            ).onfinish = function () {
                statusEl.remove();
            };
        }, 3000);
    }

    const usernameError = document.getElementById('username-error');
    const usernameHint = document.getElementById('username-hint');
    if (usernameError && usernameHint) {
        setTimeout(function () {
            usernameError.animate(
                [{ opacity: 1 }, { opacity: 0 }],
                { duration: 250, easing: 'ease-in' }
            ).onfinish = function () {
                usernameError.remove();
                usernameHint.classList.remove('hidden');
            };
        }, 3000);
    }

    const bioInput = document.getElementById('bio');
    const bioCount = document.getElementById('bio-count');
    if (bioInput) {
        const BIO_FILTER = /[\p{Cc}\p{Zl}\p{Zp}]/gu;

        function updateBioCount() {
            bioCount.textContent = bioInput.value.length + '/500';
        }

        bioInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        bioInput.addEventListener('input', function () {
            const cleaned = this.value.replace(BIO_FILTER, '');
            if (cleaned !== this.value) {
                this.value = cleaned;
            }
            updateBioCount();
        });

        updateBioCount();
    }
});
