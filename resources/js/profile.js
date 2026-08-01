import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const MAX_BUDGET_BYTES = 20000;

const COMPRESS_STEPS = [
    [512, 0.8],
    [384, 0.75],
    [256, 0.7],
    [192, 0.6],
    [128, 0.5],
];

document.addEventListener('DOMContentLoaded', function () {
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarModal = document.getElementById('avatar-modal');
    const cameraModal = document.getElementById('camera-modal');
    const cameraVideo = document.getElementById('camera-video');
    const cameraCanvas = document.getElementById('camera-canvas');
    const captureInput = document.getElementById('avatar-capture-input');
    const avatarFormToken = document.querySelector('#avatar-form input[name="_token"]');
    const cropModal = document.getElementById('crop-modal');
    const cropImage = document.getElementById('crop-image');
    const discardCropConfirm = document.getElementById('discard-crop-confirm');
    const accountForm = document.getElementById('account-form');
    const usernameConfirm = document.getElementById('username-confirm');
    const logoutForm = document.getElementById('logout-form');
    const logoutConfirm = document.getElementById('logout-confirm');

    let cameraStream = null;
    let cropper = null;
    let usernameConfirmed = false;
    let logoutConfirmed = false;

    function setModalVisible(modal, visible) {
        modal.classList.toggle('hidden', !visible);
        modal.classList.toggle('flex', visible);
    }

    function fadeIn(modal) {
        modal.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 150, easing: 'ease-out' });
    }

    function canvasToBlob(canvas, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(resolve, 'image/jpeg', quality);
        });
    }

    async function compressToBudget(canvas) {
        let lastBlob = null;

        for (const [size, quality] of COMPRESS_STEPS) {
            const output = document.createElement('canvas');
            output.width = size;
            output.height = size;
            output.getContext('2d').drawImage(canvas, 0, 0, size, size);

            const blob = await canvasToBlob(output, quality);
            lastBlob = blob;

            if (blob.size <= MAX_BUDGET_BYTES) {
                return blob;
            }
        }

        return lastBlob;
    }

    function uploadAvatar(blob) {
        const formData = new FormData();
        formData.append('_token', avatarFormToken.value);
        formData.append('avatar', blob, 'avatar.jpg');

        fetch('/profile/avatar', { method: 'POST', body: formData })
            .then(function (response) {
                if (response.redirected) {
                    window.location.href = response.url;
                }
            })
            .catch(function () {});
    }

    function openCropModal(imageUrl) {
        cropImage.src = imageUrl;
        setModalVisible(cropModal, true);
        fadeIn(cropModal);
        cropper = new Cropper(cropImage, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            background: false,
        });
    }

    window.cancelCrop = function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        cropImage.removeAttribute('src');
        setModalVisible(cropModal, false);
    };

    window.showDiscardCropConfirm = function () {
        setModalVisible(discardCropConfirm, true);
        fadeIn(discardCropConfirm);
    };

    window.hideDiscardCropConfirm = function () {
        setModalVisible(discardCropConfirm, false);
    };

    window.discardCrop = function () {
        hideDiscardCropConfirm();
        cancelCrop();
    };

    window.confirmCrop = async function () {
        if (!cropper) return;

        const croppedCanvas = cropper.getCroppedCanvas({ width: 512, height: 512 });
        const compressedBlob = await compressToBudget(croppedCanvas);
        if (!compressedBlob) {
            cancelCrop();
            return;
        }

        avatarPreview.src = URL.createObjectURL(compressedBlob);
        cancelCrop();
        uploadAvatar(compressedBlob);
    };

    window.previewAvatar = function (input) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            openCropModal(e.target.result);
        };
        reader.readAsDataURL(file);
        input.value = '';
    };

    window.openAvatarModal = function () {
        setModalVisible(avatarModal, true);
        fadeIn(avatarModal);
    };

    window.hideAvatarModal = function () {
        setModalVisible(avatarModal, false);
    };

    window.openCamera = async function () {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: 1280, height: 720 },
                audio: false,
            });
            cameraVideo.srcObject = cameraStream;
            setModalVisible(cameraModal, true);
        } catch (error) {
            captureInput.click();
        }
    };

    window.closeCamera = function () {
        if (cameraStream) {
            cameraStream.getTracks().forEach(function (track) { track.stop(); });
            cameraStream = null;
        }
        cameraVideo.srcObject = null;
        setModalVisible(cameraModal, false);
    };

    window.captureAvatar = function () {
        const width = cameraVideo.videoWidth;
        const height = cameraVideo.videoHeight;
        if (!width || !height) return;

        const size = 512;
        const side = Math.min(width, height);
        const context = cameraCanvas.getContext('2d');
        cameraCanvas.width = size;
        cameraCanvas.height = size;
        context.drawImage(cameraVideo, (width - side) / 2, (height - side) / 2, side, side, 0, 0, size, size);

        closeCamera();
        openCropModal(cameraCanvas.toDataURL('image/jpeg', 0.9));
    };

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
