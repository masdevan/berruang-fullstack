export const FILE_ICONS = {
    'file-doc': 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
    'file-image': 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z',
    'file-video': 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z',
    'file-archive': 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
};

export const FILE_POOL = [
    ['file-doc', 'brand-assets-v2.pdf', '5.4 MB'],
    ['file-image', 'og-cover.png', '980 KB'],
    ['file-video', 'feature-overview.mp4', '18.3 MB'],
    ['file-archive', 'source-files.zip', '27.5 MB'],
    ['file-doc', 'sprint-planning.docx', '1.6 MB'],
    ['file-image', 'dark-mode-mockup.png', '1.3 MB'],
    ['file-doc', 'release-notes.pdf', '740 KB'],
    ['file-video', 'tutorial-clip.mp4', '12.8 MB'],
    ['file-archive', 'backup-export.zip', '51.2 MB'],
    ['file-image', 'banner-concept.png', '2.5 MB'],
];

export function fileItemHtml(icon, name, size) {
    return `<div class="flex items-center gap-2 p-1.5 rounded hover:bg-white/5 transition-colors cursor-pointer group">
                <div class="w-7 h-7 rounded bg-white/5 flex items-center justify-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5 text-white/35">
                        <path stroke-linecap="round" stroke-linejoin="round" d="${FILE_ICONS[icon]}"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-medium text-white/65 truncate">${name}</p>
                    <p class="text-[9px] text-white/25 truncate">${size}</p>
                </div>
                <button type="button" class="shrink-0 text-white/20 hover:text-white transition-colors cursor-pointer" title="Download">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                </button>
            </div>`;
}
