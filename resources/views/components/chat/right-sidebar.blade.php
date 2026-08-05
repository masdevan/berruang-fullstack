<div id="rightbar-root" class="h-full border-l border-white/6 bg-[#0F0F0F] relative overflow-hidden flex flex-col">
    <div id="rightbar-empty" class="hidden absolute inset-0 z-10 flex-col items-center justify-center gap-2 px-8 text-center bg-[#0F0F0F]">
        <x-icons.ghost class="w-9 h-9 text-white/15" />
        <p class="text-[11px] text-white/20">Pick a conversation</p>
        <p class="text-[10px] text-white/10 leading-relaxed">Profile, shared media<br>and files will appear here</p>
    </div>
    <div id="rightbar-workspace" class="hidden absolute inset-0 z-20 bg-[#0F0F0F] flex-col">
        <div class="p-4 text-center border-b border-white/6">
            <div class="relative w-12 h-12 mx-auto">
                <span id="rightbar-ws-avatar" class="w-full h-full rounded-full bg-[#E091A9]/15 flex items-center justify-center text-sm font-medium text-[#E091A9] block"></span>
            </div>
            <p id="rightbar-ws-name" class="text-xs font-medium mt-1.5 text-white/80"></p>
            <p id="rightbar-ws-code" class="text-[10px] text-white/40 mt-0.5"></p>
        </div>
        <div class="p-3 border-b border-white/6">
            <x-chat.section-label title="About" info="Workspace information." />
            <p id="rightbar-ws-about" class="text-[11px] text-white/60 leading-relaxed"></p>
        </div>
        <div class="flex border-b border-white/6">
            <button type="button" onclick="switchWorkspaceRightbarTab('general')" id="ws-rb-general" class="flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 -mb-px text-white border-[#E091A9] transition-colors">General</button>
            <button type="button" onclick="switchWorkspaceRightbarTab('members')" id="ws-rb-members" class="flex-1 py-2.5 text-xs font-medium cursor-pointer border-b-2 -mb-px text-white/40 border-transparent transition-colors">Members</button>
        </div>
        <div id="ws-rb-general-pane" class="flex-1 overflow-y-auto">
            <div class="p-3 border-b border-white/6">
                <x-chat.section-label title="Shared Media" info="Images and videos shared in this workspace." />
                <div class="empty-state flex flex-col items-center justify-center gap-2 py-8 text-center">
                    <x-icons.ghost class="w-8 h-8 text-white/15" />
                    <p class="text-[11px] text-white/20">No shared media yet</p>
                    <p class="text-[10px] text-white/10">Photos and videos will appear here</p>
                </div>
            </div>
            <div class="px-3 pt-3 pb-2">
                <x-chat.section-label title="Shared Files" info="All files shared in this workspace." />
                <div class="empty-state flex flex-col items-center justify-center gap-2 py-8 text-center">
                    <x-icons.ghost class="w-8 h-8 text-white/15" />
                    <p class="text-[11px] text-white/20">No shared files yet</p>
                    <p class="text-[10px] text-white/10">All attachments will appear here</p>
                </div>
            </div>
        </div>
        <div id="ws-rb-members-pane" class="hidden flex-1 overflow-y-auto">
            <div class="empty-state flex flex-col items-center justify-center min-h-full gap-2 py-10 text-center">
                <x-icons.ghost class="w-8 h-8 text-white/15" />
                <p class="text-[11px] text-white/20">No members yet</p>
            </div>
        </div>
    </div>
    <div id="rightbar-view" class="hidden absolute inset-0 z-20 bg-[#0F0F0F] flex-col">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-white/6 bg-[#0A0A0A] shrink-0">
            <button type="button" onclick="closeSharedView()" class="w-7 h-7 flex items-center justify-center text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0" title="Back">
                <x-icons.chevron-left class="w-4 h-4" />
            </button>
            <p id="rightbar-view-title" class="text-xs font-medium leading-none truncate"></p>
            <button type="button" onclick="closeSharedView()" class="w-7 h-7 flex items-center justify-center text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0 ml-auto" title="Close">
                <x-icons.x class="w-4 h-4" />
            </button>
        </div>
        <div id="rightbar-view-list" class="flex-1 overflow-y-auto p-3"></div>
    </div>
    <div class="flex-1 overflow-hidden flex flex-col">
        <div id="rightbar-profile" class="p-4 text-center border-b border-white/6">
            <div class="relative w-12 h-12 mx-auto">
                <span id="rightbar-avatar" class="w-full h-full rounded-full bg-white/8 flex items-center justify-center text-sm font-medium text-white/60 block">AP</span>
                <div id="rightbar-online-dot" class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#0F0F0F] bg-white/20"></div>
            </div>
            <span id="rightbar-unsaved-badge" onclick="openSaveContactModal()" title="Save contact" class="unsaved-badge hidden flex items-center justify-center min-w-[48px] text-[8px] font-medium text-white/35 bg-white/8 rounded-full px-1.5 py-0.5 cursor-pointer hover:bg-green-500 hover:text-[#0A0A0A] transition-colors">unsaved</span>
            <p id="rightbar-custom-name" class="text-xs font-medium mt-1.5 text-[#E091A9] hidden"></p>
            <p id="rightbar-real-name" class="text-xs font-medium mt-1.5 text-white/80 hidden">
                <span class="inline-flex items-center gap-1.5 justify-center w-full">
                    <span id="rightbar-real-name-text"></span>
                </span>
            </p>
            <p id="rightbar-username" class="text-[10px] text-white/40 mt-0.5 hidden"></p>
            <button type="button" id="rightbar-save-contact" onclick="openSaveContactModal()" title="Save contact" class="hidden text-[9px] font-medium text-[#0A0A0A] bg-green-500 hover:bg-green-400 rounded-full px-3 py-1 transition-colors cursor-pointer mt-2 mx-auto">Save contact</button>
        </div>

        <div id="rightbar-about" class="p-3 border-b border-white/6">
            <x-chat.section-label title="About" info="Personal information of this contact." />
            <p id="rightbar-about-text" onclick="openBioModal()" title="Click to read more" class="text-[11px] text-white/60 leading-relaxed cursor-pointer hover:text-white/80 transition-colors" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden"></p>
        </div>

        <div class="p-3 border-b border-white/6">
            <x-chat.section-label title="Shared Media" info="Images and videos shared in this conversation.">
                <button type="button" id="shared-media-viewall" onclick="openSharedView('media')" class="hidden text-[9px] font-medium text-[#E091A9] hover:text-[#E8A8BC] transition-colors cursor-pointer">View all</button>
            </x-chat.section-label>
            <div id="shared-media-empty" class="empty-state flex flex-col items-center justify-center gap-2 py-8 text-center">
                <x-icons.ghost class="w-8 h-8 text-white/15" />
                <p class="text-[11px] text-white/20">No shared media yet</p>
                <p class="text-[10px] text-white/10">Photos and videos will appear here</p>
            </div>
            <div id="shared-media-list" class="hidden grid grid-cols-3 gap-1.5 pt-2"></div>
        </div>

        <div class="px-3 pt-3 pb-2 flex-1 overflow-hidden flex flex-col min-h-0">
            <x-chat.section-label title="Shared Files" info="All files shared in this conversation.">
                <button type="button" id="shared-files-viewall" onclick="openSharedView('files')" class="hidden text-[9px] font-medium text-[#E091A9] hover:text-[#E8A8BC] transition-colors cursor-pointer">View all</button>
            </x-chat.section-label>
            <div id="shared-files-empty" class="empty-state flex flex-col items-center justify-center gap-2 py-8 text-center flex-1">
                <x-icons.ghost class="w-8 h-8 text-white/15" />
                <p class="text-[11px] text-white/20">No shared files yet</p>
                <p class="text-[10px] text-white/10">All attachments will appear here</p>
            </div>
            <div id="shared-files-list" class="hidden flex-1 overflow-y-auto pt-2 space-y-1.5"></div>
        </div>
    </div>
</div>
