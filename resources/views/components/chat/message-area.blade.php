<div class="flex-1 min-w-0 flex-col hidden md:flex min-h-0" id="message-area">
    <div id="chat-workspace" class="flex-1 min-w-0 flex-col hidden min-h-0">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-white/6 bg-[#0A0A0A]">
        <button onclick="toggleLeft()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0" title="Toggle sidebar">
            <x-icons.dots-grid />
        </button>
        <div id="chat-header-info" class="flex items-center gap-2 min-w-0">
            <div class="w-7 h-7 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60 shrink-0" id="chat-header-avatar"></div>
            <div class="min-w-0">
                <p class="text-xs font-medium leading-none truncate transition-colors duration-150" id="chat-header-name"></p>
                <p class="flex items-center gap-1 text-[10px] leading-none text-green-400/70 mt-1" id="chat-header-status">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block -mt-0.5"></span>
                    Online
                </p>
            </div>
        </div>
        <button onclick="toggleRight()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0 ml-auto" title="Toggle profile">
            <x-icons.info />
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-4 py-3" id="messages-container"></div>

    <x-chat.message-input />
    </div>

    <div id="no-chat" class="flex-1 min-w-0 flex flex-col items-center justify-center gap-2.5">
        <x-icons.ghost class="w-10 h-10 text-white/10 shrink-0" />
        <p class="text-xs font-medium text-white/40">Select a conversation</p>
        <p class="text-[10px] text-white/20">Pick a chat or workspace to start messaging</p>
    </div>
</div>
