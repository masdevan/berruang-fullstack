@extends('layouts.app')

@section('title', 'Messages')

@section('content')
    <div id="sidebar-left" class="shrink-0 overflow-hidden w-full md:w-80 md:block">
        <x-chat.conversation-list />
    </div>
    <div class="hidden md:block w-1 shrink-0 cursor-col-resize bg-transparent hover:bg-[#E091A9]/20 transition-colors" id="resize-left" title="Drag to resize"></div>
    <x-chat.message-area />
    <div class="hidden md:block w-1 shrink-0 cursor-col-resize bg-transparent hover:bg-[#E091A9]/20 transition-colors" id="resize-right" title="Drag to resize"></div>
    <div id="sidebar-right" class="shrink-0 overflow-hidden fixed inset-y-0 right-0 z-40 w-72 translate-x-full transition-transform duration-200 md:static md:translate-x-0 md:transition-none {{ request('chat') ? 'md:w-72' : 'md:w-0' }}">
        <x-chat.right-sidebar />
    </div>

    <div id="media-modal" class="hidden fixed inset-0 z-50 bg-black/80 items-center justify-center p-4" onclick="closeMediaModal()">
        <button type="button" onclick="closeMediaModal()"
                class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white/60 hover:text-white hover:bg-white/20 transition-colors cursor-pointer">
            <x-icons.x class="w-4 h-4" />
        </button>
        <div id="media-modal-content" class="w-full max-w-5xl flex items-center justify-center" onclick="event.stopPropagation()">
            <img id="media-modal-image" src="" alt="Media preview" class="hidden w-full max-h-[90vh] object-contain rounded-lg">
            <video id="media-modal-video" src="" controls class="hidden w-full max-h-[90vh] rounded-lg bg-black"></video>
        </div>
    </div>
    <x-modal id="add-user-modal" title="Add user">
        <input type="text" id="add-user-input" placeholder="Username" autocomplete="off" data-autofocus
               class="w-full px-3 py-2 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 rounded-lg">
        <div class="flex justify-end gap-2 mt-3">
            <button type="button" onclick="closeModal('add-user-modal')" class="px-2.5 py-1.5 text-xs font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="submitAddUser()" class="px-2.5 py-1.5 text-xs font-medium bg-[#E091A9] text-[#0A0A0A] rounded-lg hover:bg-[#E8A8BC] transition-colors cursor-pointer">Add</button>
        </div>
    </x-modal>
</div>
@endsection
