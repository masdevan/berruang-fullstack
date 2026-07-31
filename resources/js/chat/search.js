let searchSpinnerTimer = null;

window.toggleSearch = function () {
    const bar = document.getElementById('search-bar');
    const input = document.getElementById('search-input');
    const opening = bar.classList.contains('hidden');
    bar.classList.toggle('hidden');
    if (opening) {
        bar.animate(
            [{ opacity: 0, transform: 'translateY(-4px)' }, { opacity: 1, transform: 'translateY(0)' }],
            { duration: 150, easing: 'ease-out' }
        );
        input.focus();
    }
};

window.filterLists = function () {
    const input = document.getElementById('search-input');
    const val = input.value.toLowerCase().trim();
    const isWorkspace = !document.getElementById('tab-pane-workspace').classList.contains('hidden');
    const selector = isWorkspace ? '[data-workspace]' : '[data-conversation]';
    document.querySelectorAll(selector).forEach(el => {
        el.style.display = val ? (el.dataset.conversation || el.dataset.workspace || '').includes(val) ? '' : 'none' : '';
    });

    if (searchSpinnerTimer) {
        clearTimeout(searchSpinnerTimer);
    }
    if (val) {
        document.getElementById('search-icon').classList.add('hidden');
        document.getElementById('search-spinner').classList.remove('hidden');
        searchSpinnerTimer = setTimeout(function () {
            document.getElementById('search-icon').classList.remove('hidden');
            document.getElementById('search-spinner').classList.add('hidden');
        }, 500);
    } else {
        document.getElementById('search-icon').classList.remove('hidden');
        document.getElementById('search-spinner').classList.add('hidden');
    }
};

window.searchConversations = function () {
    window.filterLists();
};
