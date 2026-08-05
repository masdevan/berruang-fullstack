@props(['users' => [], 'meta' => [], 'drafts' => [], 'workspaces' => collect()])
<div id="leftbar-root" class="relative h-full border-r border-white/6 flex flex-col bg-[#0F0F0F]">
    <div class="flex items-center justify-between px-4 py-3 border-b border-white/6">
        <a href="{{ url('/messages') }}" class="hover:opacity-80 transition-opacity" title="Messages">
            <img src="{{ asset('logo.png') }}" alt="BerRuang" class="h-7">
        </a>
        <div class="flex items-center gap-2">
            <button onclick="toggleSearch()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer" title="Search">
                <x-icons.search class="w-5 h-5" />
            </button>
            <a href="{{ route('profile') }}" class="block w-7 h-7 rounded-full overflow-hidden hover:ring-2 hover:ring-[#E091A9]/50 transition-all" title="Profile">
                <img src="{{ auth()->user()->avatarUrl(28) }}" alt="Profile" class="w-full h-full object-cover">
            </a>
        </div>
    </div>

    <div class="flex border-b border-white/6">
        <button type="button" onclick="switchTab('chat')" id="tab-btn-chat"
                class="flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 -mb-px text-white border-[#E091A9] transition-colors">
            <span class="inline-flex items-center gap-1.5">
                <span id="chat-unread-total" class="invisible shrink-0 min-w-3 h-3 rounded-full bg-[#E091A9] text-[#0A0A0A] text-[7px] font-semibold flex items-center justify-center px-0.5 leading-none self-center">0</span>
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
                <x-icons.spinner id="search-spinner" class="hidden w-3.5 h-3.5 animate-spin" />
            </button>
        </div>
    </div>

    <div id="tab-pane-chat" class="flex flex-col flex-1 min-h-0">
        <div class="flex-1 overflow-y-auto">
            <x-chat.conversation-list-items :users="$users" :meta="$meta ?? []" :drafts="$drafts ?? []" />
            <div id="contacts-sentinel" class="h-2"></div>
            @if ($users->isEmpty())
                <div class="empty-state flex flex-col items-center justify-center h-full gap-2 px-8 text-center">
                    <x-icons.ghost class="w-9 h-9 text-white/15" />
                    <p class="text-[11px] text-white/20">No conversations yet</p>
                    <p class="text-[10px] text-white/10 leading-relaxed">Tap + to add a user<br>and start chatting</p>
                </div>
            @endif
        </div>
    </div>

    <div id="tab-pane-workspace" class="hidden flex-1 flex-col min-h-0">
        <div class="flex-1 overflow-y-auto">
            <div id="workspace-list">
                <x-chat.workspace-list :workspaces="$workspaces" />
            </div>
            @if ($workspaces->isEmpty())
                <div id="workspace-empty" class="empty-state flex flex-col items-center justify-center h-full gap-2 px-8 text-center">
                    <x-icons.ghost class="w-9 h-9 text-white/15" />
                    <p class="text-[11px] text-white/20">No workspaces yet</p>
                    <p class="text-[10px] text-white/10 leading-relaxed">Create or join a workspace<br>to collaborate with your team</p>
                </div>
            @endif
        </div>
    </div>

    <div id="fab-menu" class="hidden absolute bottom-16 left-4 z-20 w-44 bg-[#1A1A1A] border border-white/10 rounded-lg p-1 shadow-xl">
        <button type="button" onclick="openModal('add-user-modal')" class="w-full flex items-center gap-2 px-2.5 py-2 rounded hover:bg-white/5 transition-colors cursor-pointer text-xs text-white/80">
            <x-icons.contact class="w-3.5 h-3.5 text-[#E091A9]/70 shrink-0" />
            Add user
        </button>
        <button type="button" onclick="openModal('create-workspace-modal')" class="w-full flex items-center gap-2 px-2.5 py-2 rounded hover:bg-white/5 transition-colors cursor-pointer text-xs text-white/80">
            <x-icons.workspace class="w-3.5 h-3.5 text-[#E091A9]/70 shrink-0" />
            Create workspace
        </button>
        <button type="button" onclick="openModal('join-workspace-modal')" class="w-full flex items-center gap-2 px-2.5 py-2 rounded hover:bg-white/5 transition-colors cursor-pointer text-xs text-white/80">
            <x-icons.join class="w-3.5 h-3.5 text-[#E091A9]/70 shrink-0" />
            Join workspace
        </button>
    </div>
    <button id="fab-btn" type="button" onclick="toggleFabMenu(event)" class="absolute bottom-4 left-4 z-20 w-10 h-10 rounded-full bg-[#E091A9] text-[#0A0A0A] flex items-center justify-center shadow-lg hover:bg-[#E8A8BC] transition-all duration-200 cursor-pointer" title="Add">
        <x-icons.plus class="w-4 h-4 transition-transform duration-200" />
    </button>
</div>
