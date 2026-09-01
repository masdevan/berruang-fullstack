<div id="rightbar-root" class="h-full border-l border-white/6 bg-[#0F0F0F] relative overflow-hidden flex flex-col">
    <button type="button" onclick="closeRightbar()" title="Close"
            class="md:hidden absolute top-[calc(0.75rem+env(safe-area-inset-top))] right-[calc(0.75rem+env(safe-area-inset-right))] z-30 w-8 h-8 flex items-center justify-center text-white/40 hover:text-white transition-colors cursor-pointer">
        <x-icons.x class="w-4 h-4" />
    </button>
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
            <p id="rightbar-ws-created" class="text-[10px] text-white/20 mt-0.5"></p>
            <button type="button" id="rightbar-ws-configure" onclick="openWorkspaceConfig()" class="hidden mt-2.5 px-3 py-1.5 rounded-sm bg-white/5 hover:bg-white/10 text-[10px] font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Configure workspace</button>
            <button type="button" id="rightbar-ws-leave" onclick="openLeaveWorkspace()" class="mt-2 px-3 py-1.5 rounded-sm bg-white/5 hover:bg-white/10 text-[10px] font-medium text-red-400/80 hover:text-red-400 transition-colors cursor-pointer">Leave workspace</button>
        </div>
        <div class="p-3 border-b border-white/6">
            <x-chat.section-label title="About" info="Workspace information." />
            <p id="rightbar-ws-about" onclick="openWorkspaceBio()" title="Click to read more" class="text-[11px] text-white/60 leading-relaxed cursor-pointer hover:text-white/80 transition-colors"></p>
        </div>
        <div class="flex overflow-hidden">
            <button type="button" onclick="switchWorkspaceRightbarTab('general')" id="ws-rb-general" class="flex-1 py-2.5 text-xs font-medium whitespace-nowrap cursor-pointer border-b-2 text-white border-[#E091A9] transition-colors">General</button>
            <button type="button" onclick="switchWorkspaceRightbarTab('members')" id="ws-rb-members" class="flex-1 py-2.5 text-xs font-medium whitespace-nowrap cursor-pointer border-b-2 text-white/40 border-white/6 transition-colors">Members</button>
        </div>
        <div id="ws-rb-general-pane" class="flex-1 overflow-y-auto">
            <div class="p-3 border-b border-white/6">
                <x-chat.section-label title="Shared Media" info="Images and videos shared in this workspace.">
                    <button type="button" id="ws-shared-media-viewall" onclick="openSharedView('media')" class="hidden text-[9px] font-medium text-[#E091A9] hover:text-[#E8A8BC] transition-colors cursor-pointer">View all</button>
                </x-chat.section-label>
                <div id="ws-shared-media-empty" class="empty-state flex flex-col items-center justify-center gap-2 py-8 text-center">
                    <x-icons.ghost class="w-8 h-8 text-white/15" />
                    <p class="text-[11px] text-white/20">No shared media yet</p>
                    <p class="text-[10px] text-white/10">Photos and videos will appear here</p>
                </div>
                <div id="ws-shared-media-list" class="hidden grid grid-cols-3 gap-1.5 pt-2"></div>
            </div>
            <div class="px-3 pt-3 pb-2">
                <x-chat.section-label title="Shared Files" info="All files shared in this workspace.">
                    <button type="button" id="ws-shared-files-viewall" onclick="openSharedView('files')" class="hidden text-[9px] font-medium text-[#E091A9] hover:text-[#E8A8BC] transition-colors cursor-pointer">View all</button>
                </x-chat.section-label>
                <div id="ws-shared-files-empty" class="empty-state flex flex-col items-center justify-center gap-2 py-8 text-center">
                    <x-icons.ghost class="w-8 h-8 text-white/15" />
                    <p class="text-[11px] text-white/20">No shared files yet</p>
                    <p class="text-[10px] text-white/10">All attachments will appear here</p>
                </div>
                <div id="ws-shared-files-list" class="hidden pt-2 space-y-1.5"></div>
            </div>
        </div>
        <div id="ws-rb-members-pane" class="hidden flex-1 flex-col min-h-0">
            <div id="ws-rb-members-header" class="hidden items-center justify-start gap-2 pl-2 pr-3 py-2 border-b border-white/6 shrink-0">
                <button type="button" id="ws-add-member-btn" onclick="openAddWorkspaceMemberModal()" class="inline-flex items-center gap-1 px-2 py-1 rounded-sm bg-[#E091A9]/10 hover:bg-[#E091A9]/20 text-[#E091A9] text-[10px] font-medium transition-colors cursor-pointer">
                    <x-icons.plus class="w-3 h-3" /> Add member
                </button>
                <button type="button" id="ws-bulk-kick-btn" data-bulk="toggle" class="inline-flex items-center gap-1 px-2 py-1 rounded-sm bg-white/5 hover:bg-white/10 text-white/50 hover:text-white text-[10px] font-medium transition-colors cursor-pointer" title="Remove members">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                    <span id="ws-bulk-label">Remove</span>
                </button>
            </div>
            <div class="flex-1 min-h-0 relative overflow-y-auto">
                <div id="ws-rb-members-list"></div>
                <div id="ws-rb-members-empty" class="empty-state absolute inset-0 flex flex-col items-center justify-center gap-2 text-center">
                    <x-icons.ghost class="w-8 h-8 text-white/15" />
                    <p class="text-[11px] text-white/20">No members yet</p>
                </div>
            </div>
        </div>
    </div>
    <div id="rightbar-workspace-config" class="hidden absolute inset-0 z-30 bg-[#0F0F0F] flex-col">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-white/6 bg-[#0A0A0A] shrink-0">
            <button type="button" onclick="closeWorkspaceConfig()" class="w-7 h-7 flex items-center justify-center text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0" title="Back">
                <x-icons.chevron-left class="w-4 h-4" />
            </button>
            <p class="text-xs font-medium leading-none">Configure workspace</p>
            <button type="button" onclick="closeWorkspaceConfig()" class="w-7 h-7 flex items-center justify-center text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0 ml-auto" title="Close">
                <x-icons.x class="w-4 h-4" />
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-3 space-y-3">
            <div>
                <p class="text-[10px] font-medium text-white/35 uppercase tracking-wider mb-1.5">Workspace avatar</p>
                <div class="flex items-center gap-3">
                    <div class="relative shrink-0 w-12 h-12">
                        <span id="ws-config-avatar-fallback" class="w-full h-full rounded-full bg-[#E091A9]/15 flex items-center justify-center text-sm font-medium text-[#E091A9]"></span>
                        <img id="ws-config-avatar" alt="" class="hidden absolute inset-0 w-full h-full rounded-full object-cover">
                    </div>
                    <button type="button" onclick="openAvatarModal()" class="px-2.5 py-1.5 rounded-sm bg-white/5 hover:bg-white/10 text-[10px] font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Change picture</button>
                </div>
            </div>
            <div>
                <p class="text-[10px] font-medium text-white/35 uppercase tracking-wider mb-1.5">About</p>
                <textarea id="ws-config-bio" rows="3" maxlength="500" placeholder="Tell people what this workspace is about" class="w-full px-3 py-2.5 pb-5 bg-white/3 border border-white/6 text-xs text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200 rounded-sm resize-none"></textarea>
            </div>
            <div>
                <p class="text-[10px] font-medium text-white/35 uppercase tracking-wider mb-1.5">Workspace code</p>
                <div class="relative">
                    <input type="text" id="ws-config-code" maxlength="8" autocomplete="off" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')" class="w-full px-3 py-2.5 pr-10 tracking-[0.25em] text-center text-xs bg-white/3 border border-white/6 text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200 rounded-sm">
                    <button type="button" onclick="rollWorkspaceCode()" class="absolute right-2 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/70 transition-colors cursor-pointer" title="Random code">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    </button>
                </div>
            </div>
            <p id="ws-config-error" class="hidden text-[10px] text-red-400"></p>
            <button type="button" onclick="saveWorkspaceConfig()" class="w-full py-2 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-xs font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Save changes</button>
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
            <div class="relative w-16 h-16 md:w-12 md:h-12 mx-auto">
                <span id="rightbar-avatar" class="w-full h-full rounded-full bg-white/8 flex items-center justify-center text-base md:text-sm font-medium text-white/60 block">AP</span>
                <div id="rightbar-online-dot" class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#0F0F0F] bg-white/20"></div>
            </div>
            <span id="rightbar-unsaved-badge" onclick="openSaveContactModal()" title="Save contact" class="unsaved-badge hidden flex items-center justify-center w-14 mx-auto mt-2 text-[8px] font-medium text-white/35 bg-white/8 rounded-full px-2 py-0.5 cursor-pointer hover:bg-green-500 hover:text-[#0A0A0A] transition-colors">unsaved</span>
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
