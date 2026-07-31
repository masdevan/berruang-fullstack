import './register.js';
import './verify-email.js';

document.addEventListener('DOMContentLoaded', function () {
    const alert = document.getElementById('auth-alert');
    if (alert) {
        setTimeout(() => alert.remove(), 3000);
    }
});

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.password-toggle');
    if (!btn) return;

    const input = btn.parentElement.querySelector('input');
    const eye = btn.querySelector('.icon-eye');
    const eyeOff = btn.querySelector('.icon-eye-off');

    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.add('hidden');
        eyeOff.classList.remove('hidden');
    } else {
        input.type = 'password';
        eye.classList.remove('hidden');
        eyeOff.classList.add('hidden');
    }
});

window.checkPasswordStrength = function (password) {
    const container = document.getElementById('password-strength');
    const bars = [0, 1, 2].map(i => document.getElementById('str-' + i));
    const label = document.getElementById('strength-label');

    if (!password) {
        container.classList.add('hidden');
        return;
    }
    container.classList.remove('hidden');

    let score = 0;
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password) && /[^a-zA-Z0-9]/.test(password)) score++;

    const states = [
        { level: 0, bars: ['bg-white/6', 'bg-white/6', 'bg-white/6'], text: '', color: 'text-white/30' },
        { level: 1, bars: ['bg-red-500', 'bg-white/6', 'bg-white/6'], text: 'Weak', color: 'text-red-400' },
        { level: 2, bars: ['bg-yellow-500', 'bg-yellow-500', 'bg-white/6'], text: 'Medium', color: 'text-yellow-400' },
        { level: 3, bars: ['bg-green-500', 'bg-green-500', 'bg-green-500'], text: 'Strong', color: 'text-green-400' },
    ];

    const state = states[score];
    bars.forEach((bar, i) => {
        bar.className = 'h-0.5 flex-1 ' + state.bars[i] + ' transition-all duration-300';
    });
    label.textContent = state.text;
    label.className = 'text-xs ' + state.color;
};
