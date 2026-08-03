<div class="px-3 pt-1.5 pb-2.5 bg-[#0A0A0A]">
    <div id="attach-preview-bar" class="hidden flex gap-1.5 overflow-x-hidden pt-1.5 pr-2 pb-1.5 select-none"></div>
    <form class="flex items-end gap-1 bg-white/3 rounded-2xl px-1.5 py-1 focus-within:bg-white/5 transition-all" id="chat-form">
        <div class="relative shrink-0">
            <button type="button" onclick="toggleAttachMenu(event)" class="w-8 h-8 rounded-full flex items-center justify-center text-white/40 hover:text-white/70 transition-colors cursor-pointer" title="Attach">
                <x-icons.plus />
            </button>
            <div id="attach-menu" class="hidden absolute bottom-full left-0 mb-1 w-44 bg-[#1A1A1A] border border-white/10 rounded-sm p-0.5 z-50">
                <button type="button" onclick="triggerAttach('photo')" class="w-full flex items-center gap-2 px-2 py-1.5 rounded-sm hover:bg-white/5 transition-colors cursor-pointer">
                    <div class="w-5 h-5 rounded-sm bg-white/5 flex items-center justify-center text-white/50 shrink-0">
                        <x-icons.file-image class="w-3 h-3" />
                    </div>
                    <span class="text-[10px] text-white/70">Photo & Video Library</span>
                </button>
                <button type="button" class="w-full flex items-center gap-2 px-2 py-1.5 rounded-sm hover:bg-white/5 transition-colors cursor-pointer">
                    <div class="w-5 h-5 rounded-sm bg-white/5 flex items-center justify-center text-white/50 shrink-0">
                        <x-icons.camera class="w-3 h-3" />
                    </div>
                    <span class="text-[10px] text-white/70">Camera</span>
                </button>
                <button type="button" onclick="triggerAttach('document')" class="w-full flex items-center gap-2 px-2 py-1.5 rounded-sm hover:bg-white/5 transition-colors cursor-pointer">
                    <div class="w-5 h-5 rounded-sm bg-white/5 flex items-center justify-center text-white/50 shrink-0">
                        <x-icons.file-doc class="w-3 h-3" />
                    </div>
                    <span class="text-[10px] text-white/70">Document</span>
                </button>
                <button type="button" class="w-full flex items-center gap-2 px-2 py-1.5 rounded-sm hover:bg-white/5 transition-colors cursor-pointer">
                    <div class="w-5 h-5 rounded-sm bg-white/5 flex items-center justify-center text-white/50 shrink-0">
                        <x-icons.location class="w-3 h-3" />
                    </div>
                    <span class="text-[10px] text-white/70">Location</span>
                </button>
                <button type="button" class="w-full flex items-center gap-2 px-2 py-1.5 rounded-sm hover:bg-white/5 transition-colors cursor-pointer">
                    <div class="w-5 h-5 rounded-sm bg-white/5 flex items-center justify-center text-white/50 shrink-0">
                        <x-icons.contact class="w-3 h-3" />
                    </div>
                    <span class="text-[10px] text-white/70">Contact</span>
                </button>
            </div>
        </div>
        <input type="file" id="attach-file-input" class="hidden" multiple>
        <textarea id="message-input" placeholder="Type a message..." rows="1" class="flex-1 px-1.5 py-1.5 bg-transparent text-xs text-white placeholder-white/20 resize-none overflow-y-auto max-h-30 outline-none" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.08) transparent;"></textarea>
        <button type="submit" class="shrink-0 w-8 h-8 rounded-full text-[#E091A9] hover:text-[#E8A8BC] flex items-center justify-center transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer" id="send-btn">
            <x-icons.send class="w-4 h-4 ml-0.5" />
        </button>
    </form>
</div>
