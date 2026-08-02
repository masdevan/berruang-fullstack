const IDLE_AFTER_MS = 60 * 1000;
const ACTIVITY_EVENTS = ['mousemove', 'keydown', 'click', 'touchstart', 'scroll'];

let status = 'online';
let timer = null;

function send() {
    fetch('/presence-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ status: status }),
    }).catch(function () {});
}

function report(next) {
    if (next === status) return;
    status = next;
    send();
}

function armTimer() {
    clearTimeout(timer);
    timer = setTimeout(function () { report('idle'); }, IDLE_AFTER_MS);
}

document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
        clearTimeout(timer);
        report('idle');
    } else {
        report('online');
        armTimer();
    }
});

ACTIVITY_EVENTS.forEach(function (ev) {
    document.addEventListener(ev, function () {
        if (status === 'idle') report('online');
        armTimer();
    }, { passive: true });
});

setInterval(send, 60 * 1000);

armTimer();
