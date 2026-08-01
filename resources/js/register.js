let autoGen = true;

window.generateUsername = function () {
    if (!autoGen) return;
    const first = document.querySelector('[name="first_name"]').value;
    const last = document.querySelector('[name="last_name"]').value;
    const slug = (first + ' ' + last).replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '').toLowerCase();
    document.querySelector('[name="username"]').value = slug;
    checkUsername(slug);
};

document.addEventListener('DOMContentLoaded', function () {
    if (!document.querySelector('[name="username"]')) return;
    document.querySelector('[name="username"]').addEventListener('input', function () {
        if (this.value !== this.value.toLowerCase()) {
            this.value = this.value.toLowerCase();
        }
        autoGen = false;
        checkUsername(this.value);
    });

    document.querySelectorAll('[name="first_name"], [name="last_name"]').forEach(function (input) {
        input.addEventListener('input', function () {
            autoGen = true;
            generateUsername();
        });
    });
});

let timeout;
function checkUsername(val) {
    clearTimeout(timeout);
    const check = document.getElementById('username-check');
    const error = document.getElementById('username-error');
    const spinner = document.getElementById('username-spinner');
    const invalid = val && !/^[a-z0-9_]{5,}$/.test(val);
    check.classList.add('hidden');
    error.classList.add('hidden');
    spinner.classList.add('hidden');
    if (!val) return;
    if (invalid) {
        error.title = val.length < 5
            ? 'Username must be at least 5 characters'
            : 'Only lowercase letters, numbers, and underscores are allowed';
        error.classList.remove('hidden');
        return;
    }
    spinner.classList.remove('hidden');
    timeout = setTimeout(() => {
        fetch('/check-username/' + encodeURIComponent(val))
            .then(r => r.json())
            .then(d => {
                if (document.querySelector('[name="username"]').value !== val) return;
                spinner.classList.add('hidden');
                if (d.taken) {
                    error.title = 'Username already taken';
                    error.classList.remove('hidden');
                } else {
                    check.classList.remove('hidden');
                }
            })
            .catch(() => {
                spinner.classList.add('hidden');
            });
    }, 300);
}
