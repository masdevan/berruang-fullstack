const sectionTips = {};

function getSectionTip(rootId) {
    if (!sectionTips[rootId]) {
        const tip = document.createElement('div');
        tip.className = 'absolute z-50 w-48 bg-[#1A1A1A] border border-white/10 rounded-lg p-2.5 shadow-lg pointer-events-none text-[10px] font-medium normal-case tracking-normal text-white/60 leading-relaxed hidden';
        document.getElementById(rootId).appendChild(tip);
        sectionTips[rootId] = tip;
    }
    return sectionTips[rootId];
}

export function showSectionInfo(btn) {
    const rootId = btn.dataset.tipRoot;
    const tip = getSectionTip(rootId);
    const root = document.getElementById(rootId);
    const rootRect = root.getBoundingClientRect();
    const btnRect = btn.getBoundingClientRect();
    const tipWidth = 192;
    const tipHeight = tip.offsetHeight || 80;

    tip.textContent = btn.querySelector('span').textContent;

    let x = btnRect.left - rootRect.left;
    let y = btnRect.bottom - rootRect.top + 6;

    if (y + tipHeight > rootRect.height) {
        y = btnRect.top - rootRect.top - tipHeight - 6;
    }
    if (x + tipWidth > rootRect.width) {
        x = rootRect.width - tipWidth - 8;
    }

    tip.style.top = Math.max(8, y) + 'px';
    tip.style.left = Math.max(8, x) + 'px';
    tip.classList.remove('hidden');
}

export function hideSectionInfo() {
    Object.values(sectionTips).forEach(function (tip) { tip.classList.add('hidden'); });
}

window.toggleSectionInfo = function (btn) {
    const rootId = btn.dataset.tipRoot;
    const tip = getSectionTip(rootId);

    if (!tip.classList.contains('hidden') && tip.textContent === btn.querySelector('span').textContent) {
        hideSectionInfo();
    } else {
        showSectionInfo(btn);
    }
};
