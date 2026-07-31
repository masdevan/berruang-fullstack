<div id="leftbar-root" class="h-full border-r border-white/6 flex flex-col bg-[#0F0F0F]">
    <div class="flex items-center justify-between px-4 py-3 border-b border-white/6">
        <a href="{{ url('/messages') }}" class="hover:opacity-80 transition-opacity" title="Messages">
            <img src="{{ asset('logo.png') }}" alt="BerRuang" class="h-7">
        </a>
        <div class="flex items-center gap-2">
            <button onclick="toggleSearch()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer" title="Search">
                <x-icons.search class="w-5 h-5" />
            </button>
            <a href="{{ route('profile') }}" class="block w-7 h-7 rounded-full overflow-hidden hover:ring-2 hover:ring-[#E091A9]/50 transition-all" title="Profile">
                <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=D&background=2A2A2A&color=FFFFFF&size=28' }}" alt="Profile" class="w-full h-full object-cover">
            </a>
        </div>
    </div>

    <div class="flex border-b border-white/6">
        <button type="button" onclick="switchTab('chat')" id="tab-btn-chat"
                class="flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 -mb-px text-white border-[#E091A9] transition-colors">
            <span class="inline-flex items-center gap-1.5">
                Chat
                <span class="section-info-btn relative cursor-pointer text-white/25 hover:text-white/60 transition-colors shrink-0" data-tip-root="leftbar-root">
                    <x-icons.help class="w-3.5 h-3.5" />
                    <span class="hidden">Your private conversations with other members.</span>
                </span>
            </span>
        </button>
        <button type="button" onclick="switchTab('workspace')" id="tab-btn-workspace"
                class="flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 -mb-px text-white/40 border-transparent transition-colors">
            <span class="inline-flex items-center gap-1.5">
                Workspace
                <span class="section-info-btn relative cursor-pointer text-white/25 hover:text-white/60 transition-colors shrink-0" data-tip-root="leftbar-root">
                    <x-icons.help class="w-3.5 h-3.5" />
                    <span class="hidden">Shared spaces for team collaboration and discussions.</span>
                </span>
            </span>
        </button>
    </div>

    <div id="search-bar" class="p-2.5 pb-2 border-b border-white/6 hidden">
        <div class="relative">
            <input type="text" id="search-input" placeholder="Search conversations..." class="w-full pl-2.5 pr-8 py-1.5 bg-white/3 border border-white/6 text-xs text-white placeholder-white/20 rounded-sm focus:outline-none focus:border-[#E091A9]/50 transition-all">
            <button type="button" onclick="searchConversations()" class="absolute right-1.5 top-1/2 -translate-y-1/2 text-white/20 hover:text-white/60 transition-colors cursor-pointer">
                <x-icons.search class="w-3.5 h-3.5" id="search-icon" />
                <svg id="search-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="hidden w-3.5 h-3.5 animate-spin">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
            </button>
        </div>
    </div>

    <div id="tab-pane-chat" class="flex flex-col flex-1 min-h-0">
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

    <div id="tab-pane-workspace" class="hidden flex-1 flex-col min-h-0">
        <div class="flex-1 overflow-y-auto py-1">
            <x-chat.workspace-item name="BerRuang Design" avatar="BD" meta="4 members · 3 projects" />
            <x-chat.workspace-item name="Mobile App Dev" avatar="MA" meta="6 members · 5 projects" />
            <x-chat.workspace-item name="Marketing Q3" avatar="M3" meta="3 members · 2 projects" />
            <x-chat.workspace-item name="Content Team" avatar="CT" meta="5 members · 4 projects" />
            <x-chat.workspace-item name="Research & Insights" avatar="RI" meta="2 members · 1 project" />
        </div>
    </div>
</div>
