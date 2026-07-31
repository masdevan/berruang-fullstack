<div class="flex-1 min-w-0 flex-col hidden md:flex" id="message-area">
    <div id="chat-workspace" class="flex-1 min-w-0 flex-col hidden">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-white/6 bg-[#0A0A0A]">
        <button onclick="toggleLeft()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0" title="Toggle sidebar">
            <x-icons.dots-grid />
        </button>
        <div class="w-7 h-7 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60 shrink-0" id="chat-header-avatar">AP</div>
        <div>
            <p class="text-xs font-medium leading-none" id="chat-header-name">Alya Putri</p>
            <p class="flex items-center gap-1 text-[10px] leading-none text-green-400/70 mt-1" id="chat-header-status">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block -mt-0.5"></span>
                Online
            </p>
        </div>
        <button onclick="toggleRight()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0 ml-auto" title="Toggle profile">
            <x-icons.info />
        </button>
    </div>

    <div id="workspace-tabs" class="hidden items-stretch justify-between px-3 border-y border-white/10 bg-[#0A0A0A]">
        <div class="flex items-stretch">
            <button type="button" class="px-4 py-[9.5px] text-[11px] font-medium text-white border-r border-white/15 border-b-2 border-b-[#E091A9] cursor-pointer">Message</button>
        </div>
        <div class="flex items-center">
            <button type="button" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer" title="Add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <path d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-4 py-3" id="messages-container"></div>

    <x-chat.message-input />
    </div>

    <div id="no-chat" class="flex-1 min-w-0 flex flex-col items-center justify-center gap-2.5">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-white/10 shrink-0">
            <path d="M9 9h.01"/>
            <path d="M15 9h.01"/>
            <path d="M12 1a8 8 0 0 0-8 8v12l3-3 2.5 2.5L12 17.5l2.5 2.5L17 18l3 3V9a8 8 0 0 0-8-8z"/>
        </svg>
        <p class="text-xs font-medium text-white/40">Select a conversation</p>
        <p class="text-[10px] text-white/20">Pick a chat or workspace to start messaging</p>
    </div>
</div>
