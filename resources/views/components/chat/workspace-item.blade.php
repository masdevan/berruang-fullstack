@props(['name', 'avatar', 'meta' => ''])

<div class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer transition-all duration-150 hover:bg-white/5">
    <div class="w-9 h-9 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60 shrink-0">
        {{ $avatar }}
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-xs font-medium text-white/80 truncate">{{ $name }}</p>
        <p class="text-[11px] text-white/35 truncate mt-0.5">{{ $meta }}</p>
    </div>
</div>
