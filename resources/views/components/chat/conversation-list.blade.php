<div class="h-full border-r border-white/6 flex flex-col bg-[#0F0F0F]">
    <div class="flex items-center justify-between px-4 py-3 border-b border-white/6">
        <img src="{{ asset('logo.png') }}" alt="BerRuang" class="h-7">
        <div class="flex items-center gap-2">
            <button onclick="toggleSearch()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer" title="Search">
                <x-icons.search class="w-5 h-5" />
            </button>
            <a href="#" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer" title="Settings">
                <x-icons.settings />
            </a>
        </div>
    </div>
    <div id="search-bar" class="p-3 border-b border-white/6 hidden">
        <div class="relative">
            <input type="text" id="search-input" placeholder="Search conversations..." class="w-full pl-3 pr-9 py-2 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 rounded-lg focus:outline-none focus:border-[#E091A9]/50 transition-all">
            <button type="button" onclick="searchConversations()" class="absolute right-2 top-1/2 -translate-y-1/2 text-white/20 hover:text-white/60 transition-colors cursor-pointer">
                <x-icons.search />
            </button>
        </div>
    </div>
    <div class="flex-1 overflow-y-auto">
        <x-chat.conversation-item
            name="Alya Putri"
            avatar="AP"
            last-message="Okay, I'll review the design first thing tomorrow!"
            time="5m"
            online="true"
            active="true" />

        <x-chat.conversation-item
            name="Design Team"
            avatar="DT"
            last-message="Rama: New mockups are ready for feedback"
            time="1h"
            unread="3" />

        <x-chat.conversation-item
            name="Rama Wijaya"
            avatar="RW"
            last-message="Sounds good, let's finalize it by Friday"
            time="2h"
            online="true" />

        <x-chat.conversation-item
            name="Sari Dewi"
            avatar="SD"
            last-message="Thank you for the quick response!"
            time="3h" />

        <x-chat.conversation-item
            name="Budi Santoso"
            avatar="BS"
            last-message="Can you send me the file?"
            time="Yesterday" />

        <x-chat.conversation-item
            name="Marketing Team"
            avatar="MT"
            last-message="Doni: Campaign results are in"
            time="Yesterday"
            unread="7" />

        <x-chat.conversation-item
            name="Doni Prasetyo"
            avatar="DP"
            last-message="Let's discuss this in the meeting"
            time="2d" />
    </div>
</div>