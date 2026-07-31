<div class="flex-1 flex-col hidden md:flex" id="message-area">
    <div class="flex items-center gap-2 px-4 py-3 border-b border-white/6 bg-[#0A0A0A]">
        <button onclick="toggleLeft()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0" title="Toggle sidebar">
            <x-icons.dots-grid />
        </button>
        <div class="w-7 h-7 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60 shrink-0">AP</div>
        <div>
            <p class="text-xs font-medium leading-none">Alya Putri</p>
            <p class="flex items-center gap-1 text-[10px] leading-none text-green-400/70 mt-1">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block -mt-0.5"></span>
                Online
            </p>
        </div>
        <button onclick="toggleRight()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0 ml-auto" title="Toggle profile">
            <x-icons.info />
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-4 py-3 space-y-1" id="messages-container">
        <x-chat.message-bubble message="Hey! How's the design coming along?" time="10:32 AM" />

        <x-chat.message-bubble message="Almost done! Just polishing the icons." time="10:33 AM" sender="me" />

        <x-chat.message-bubble message="Great, take your time. The client loves the direction we're heading." time="10:34 AM" />

        <x-chat.message-bubble message="That's awesome to hear! I'll send you the final files by EOD." time="10:35 AM" sender="me" />

        <x-chat.message-bubble message="Perfect. Also, don't forget we have the sync meeting tomorrow at 2 PM." time="10:36 AM" />

        <x-chat.message-bubble message="Got it, I'll prepare the presentation slides." time="10:37 AM" sender="me" />

        <x-chat.message-bubble message="Awesome, see you there!" time="10:38 AM" />

        <div class="flex justify-center my-3">
            <span class="text-[11px] text-white/20 px-3 py-1 bg-white/3 rounded-full">Today</span>
        </div>

        <x-chat.message-bubble message="Okay, I'll review the design first thing tomorrow!" time="11:02 AM" />
    </div>

    <x-chat.message-input />
</div>
