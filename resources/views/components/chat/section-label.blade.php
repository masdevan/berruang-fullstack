@props(['title' => '', 'info' => ''])
<div class="flex items-center justify-between mb-2 shrink-0">
    <div class="flex items-center gap-1.5">
        <p class="text-[10px] font-medium text-white/35 uppercase tracking-wider">{{ $title }}</p>
        <button type="button" class="section-info-btn relative cursor-pointer text-white/30 hover:text-white/60 transition-colors shrink-0" data-tip-root="rightbar-root">
            <x-icons.help class="w-3.5 h-3.5" />
            <span class="hidden absolute z-30 w-48 bg-[#1A1A1A] border border-white/10 rounded-lg p-2.5 shadow-lg pointer-events-none text-[10px] font-medium normal-case tracking-normal text-white/60 leading-relaxed">{{ $info }}</span>
        </button>
    </div>
    {{ $slot }}
</div>
