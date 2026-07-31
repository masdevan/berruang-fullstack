@props(['icon' => 'file-doc', 'name' => '', 'size' => ''])
<div class="flex items-center gap-2 p-1.5 rounded hover:bg-white/5 transition-colors cursor-pointer group">
    <div class="w-7 h-7 rounded bg-white/5 flex items-center justify-center shrink-0">
        <x-dynamic-component :component="'icons.' . $icon" class="w-3.5 h-3.5 text-white/35" />
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-[11px] font-medium text-white/65 truncate">{{ $name }}</p>
        <p class="text-[9px] text-white/25 truncate">{{ $size }}</p>
    </div>
    <button type="button" class="shrink-0 text-white/20 hover:text-white transition-colors cursor-pointer" title="Download">
        <x-icons.download class="w-3.5 h-3.5" />
    </button>
</div>
