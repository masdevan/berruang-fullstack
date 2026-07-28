let autoGen = true;

window.generateUsername = function (name) {
    if (!autoGen) return;
    const slug = name.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
    document.querySelector('[name="username"]').value = slug;
    checkUsername(slug);
};

document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('[name="username"]').addEventListener('input', function () {
        if (this.value === '') return;
        autoGen = false;
        checkUsername(this.value);
    });

    document.querySelector('[name="name"]').addEventListener('input', function () {
        autoGen = true;
    });
});

let timeout;
function checkUsername(val) {
    clearTimeout(timeout);
    const status = document.getElementById('username-status');
    if (!val) { status.classList.add('hidden'); return; }
    timeout = setTimeout(() => {
        fetch('/check-username/' + encodeURIComponent(val))
            .then(r => r.json())
            .then(d => {
                status.classList.remove('hidden');
                if (d.taken) {
                    status.className = 'text-xs mt-1 text-red-400/80';
                    status.textContent = 'Username already taken';
                } else {
                    status.className = 'text-xs mt-1 text-green-400/60';
                    status.textContent = 'Username available';
                }
            });
    }, 300);
}
