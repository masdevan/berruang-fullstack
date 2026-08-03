const IDLE_AFTER_MS = 60 * 1000;
const IDLE_DELAY_MS = 1200;
const ACTIVITY_EVENTS = ['mousemove', 'keydown', 'click', 'touchstart', 'scroll'];

let status = 'online';
let timer = null;
let paused = false;
const bc = 'BroadcastChannel' in window ? new BroadcastChannel('berruang-presence') : null;

function send() {
    if (paused) return;
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

function announceActive() {
    paused = false;
    if (bc) bc.postMessage('active');
    report('online');
    armTimer();
}

if (bc) {
    bc.addEventListener('message', function (e) {
        if (e.data !== 'active') return;
        paused = true;
        clearTimeout(timer);
    });
}

document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
        clearTimeout(timer);
        timer = setTimeout(function () {
            if (document.hidden) report('idle');
        }, IDLE_DELAY_MS);
    } else {
        announceActive();
    }
});

ACTIVITY_EVENTS.forEach(function (ev) {
    document.addEventListener(ev, function () {
        if (status === 'idle') {
            paused = false;
            if (bc) bc.postMessage('active');
        }
        report('online');
        armTimer();
    }, { passive: true });
});

if (document.hidden) {
    timer = setTimeout(function () { report('idle'); }, IDLE_DELAY_MS);
} else {
    announceActive();
}

armTimer();
