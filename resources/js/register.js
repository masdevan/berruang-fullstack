let autoGen = true;

window.generateUsername = function (name) {
    if (!autoGen) return;
    const slug = name.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
    document.querySelector('[name="username"]').value = slug;
    checkUsername(slug);
};

document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('[name="username"]').addEventListener('input', function () {
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
    const check = document.getElementById('username-check');
    const spinner = document.getElementById('username-spinner');
    if (!val) {
        check.classList.add('hidden');
        spinner.classList.add('hidden');
        return;
    }
    check.classList.add('hidden');
    spinner.classList.remove('hidden');
    timeout = setTimeout(() => {
        fetch('/check-username/' + encodeURIComponent(val))
            .then(r => r.json())
            .then(d => {
                if (document.querySelector('[name="username"]').value !== val) return;
                spinner.classList.add('hidden');
                if (!d.taken) check.classList.remove('hidden');
            })
            .catch(() => {
                spinner.classList.add('hidden');
            });
    }, 300);
}
