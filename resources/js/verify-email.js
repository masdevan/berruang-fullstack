window.handleCodeInput = function (input, index) {
    input.value = input.value.replace(/\D/g, '');
    if (input.value && index < 5) {
        document.getElementById('code-' + (index + 1)).focus();
    }
};

window.handleCodeKeydown = function (event, index) {
    if (event.key === 'Backspace' && !event.target.value && index > 0) {
        document.getElementById('code-' + (index - 1)).focus();
    }
};
