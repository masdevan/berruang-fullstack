@props(['name', 'avatar', 'lastMessage' => '', 'time' => '', 'unread' => 0, 'online' => false, 'active' => false])

<div data-conversation="{{ strtolower($name) }} {{ strtolower($lastMessage) }}" onclick="openConversation()" class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer transition-all duration-150 hover:bg-white/5 {{ $active ? 'bg-white/5' : '' }}">
    <div class="relative shrink-0">
        <div class="w-9 h-9 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60">
            {{ $avatar }}
        </div>
        @if ($online)
            <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-[#0F0F0F]"></div>
        @endif
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium truncate {{ $active ? 'text-white' : 'text-white/80' }}">{{ $name }}</p>
            <p class="text-[10px] text-white/30 shrink-0 ml-2">{{ $time }}</p>
        </div>
        <div class="flex items-center justify-between mt-0.5">
            <p class="text-[11px] text-white/35 truncate">{{ $lastMessage }}</p>
            @if ($unread)
                <span class="shrink-0 ml-2 min-w-3.75 h-3.75 rounded-full bg-[#E091A9] text-[#0A0A0A] text-[7px] font-semibold flex items-center justify-center px-1 leading-none">{{ $unread }}</span>
            @endif
        </div>
    </div>
</div>
