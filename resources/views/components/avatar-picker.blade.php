@props(['formId' => 'avatar-form'])

<div id="avatar-modal" class="hidden fixed inset-0 z-60 items-center justify-center bg-black/70 p-4" onclick="hideAvatarModal()">
    <input type="file" id="avatar-capture-input" name="avatar" accept="image/*" capture="environment" form="{{ $formId }}" class="hidden" onchange="previewAvatar(this)">
    <input type="file" id="avatar-gallery-input" name="avatar" accept="image/*" form="{{ $formId }}" class="hidden" onchange="previewAvatar(this)">
    <div class="w-56 bg-[#1A1A1A] border border-white/10 rounded-sm p-1" onclick="event.stopPropagation()">
        <button type="button" onclick="hideAvatarModal(); openCamera()" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-sm hover:bg-white/5 transition-colors cursor-pointer">
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

<div id="camera-modal" class="hidden fixed inset-0 z-60 items-center justify-center bg-black/70 p-4" onclick="closeCamera()">
    <div class="w-full max-w-sm bg-[#1A1A1A] border border-white/10 rounded-sm overflow-hidden" onclick="event.stopPropagation()">
        <div class="bg-black">
            <video id="camera-video" autoplay playsinline muted class="w-full aspect-square object-cover"></video>
            <canvas id="camera-canvas" class="hidden"></canvas>
        </div>
        <div class="flex gap-2 p-3">
            <button type="button" onclick="closeCamera()" class="flex-1 py-2 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="captureAvatar()" class="flex-1 py-2 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Capture</button>
        </div>
    </div>
</div>

<div id="crop-modal" class="hidden fixed inset-0 z-60 items-center justify-center bg-black/70 p-4" onclick="showDiscardCropConfirm()">
    <div class="w-full max-w-sm bg-[#1A1A1A] border border-white/10 rounded-sm overflow-hidden" onclick="event.stopPropagation()">
        <div class="bg-black">
            <img id="crop-image" class="block w-full max-h-80 mx-auto" alt="Crop preview">
        </div>
        <div class="flex gap-2 p-3">
            <button type="button" onclick="cancelCrop()" class="flex-1 py-2 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="confirmCrop()" class="flex-1 py-2 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Save</button>
        </div>
    </div>
</div>

<div id="discard-crop-confirm" class="hidden fixed inset-0 z-60 items-center justify-center bg-black/70 p-4" onclick="hideDiscardCropConfirm()">
    <div class="w-full max-w-xs bg-[#1A1A1A] border border-white/10 rounded-sm p-4" onclick="event.stopPropagation()">
        <p class="text-xs font-medium">Discard changes?</p>
        <p class="text-[11px] text-white/50 mt-1 leading-relaxed">Your cropped photo will not be saved.</p>
        <div class="flex gap-2 mt-4">
            <button type="button" onclick="hideDiscardCropConfirm()" class="flex-1 py-1.5 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Keep editing</button>
            <button type="button" onclick="discardCrop()" class="flex-1 py-1.5 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Discard</button>
        </div>
    </div>
</div>
