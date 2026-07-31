window.handleCodeInput = function (input, index) {
    input.value = input.value.replace(/\D/g, '');
    const inputName = input.name.replace(/\[\]$/, '');
    if (input.value && index < 5) {
        document.getElementById(inputName + '-' + (index + 1)).focus();
    }
};

window.handleCodeKeydown = function (event, index) {
    if (event.key === 'Backspace' && !event.target.value && index > 0) {
        const inputName = event.target.name.replace(/\[\]$/, '');
        document.getElementById(inputName + '-' + (index - 1)).focus();
    }
};

window.handleCodePaste = function (event) {
    const pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
    if (!pasted) return;

    event.preventDefault();

    const inputName = event.target.name.replace(/\[\]$/, '');
    pasted.split('').forEach((digit, index) => {
        document.getElementById(inputName + '-' + index).value = digit;
    });
    document.getElementById(inputName + '-' + (pasted.length - 1)).focus();
};
