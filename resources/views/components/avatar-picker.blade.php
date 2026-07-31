<div id="avatar-modal" class="hidden fixed inset-0 z-[60] items-center justify-center bg-black/70 p-4" onclick="hideAvatarModal()">
    <input type="file" id="avatar-capture-input" name="avatar" accept="image/*" capture="environment" class="hidden" onchange="previewAvatar(this)">
    <input type="file" id="avatar-gallery-input" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
    <div class="w-56 bg-[#1A1A1A] border border-white/10 rounded-sm p-1" onclick="event.stopPropagation()">
        <button type="button" onclick="hideAvatarModal(); document.getElementById('avatar-capture-input').click()" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-sm hover:bg-white/5 transition-colors cursor-pointer">
            <div class="w-5 h-5 rounded-sm bg-white/5 flex items-center justify-center text-white/50 shrink-0">
                <x-icons.camera class="w-3 h-3" />
            </div>
            <span class="text-[11px] text-white/70">Take Photo</span>
        </button>
        <button type="button" onclick="hideAvatarModal(); document.getElementById('avatar-gallery-input').click()" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-sm hover:bg-white/5 transition-colors cursor-pointer">
            <div class="w-5 h-5 rounded-sm bg-white/5 flex items-center justify-center text-white/50 shrink-0">
                <x-icons.file-image class="w-3 h-3" />
            </div>
            <span class="text-[11px] text-white/70">Photo & Video Library</span>
        </button>
    </div>
</div>
