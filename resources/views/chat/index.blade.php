@extends('layouts.app')

@section('title', 'Messages')

@section('content')
    <div id="sidebar-left" class="shrink-0 overflow-hidden w-full md:w-80 md:block">
        <x-chat.conversation-list :users="$users" :meta="$meta ?? []" :drafts="$drafts ?? []" />
    </div>
    <div class="hidden md:block w-1 shrink-0 cursor-col-resize bg-transparent hover:bg-[#E091A9]/20 transition-colors" id="resize-left" title="Drag to resize"></div>
    <x-chat.message-area />
    <div class="hidden md:block w-1 shrink-0 cursor-col-resize bg-transparent hover:bg-[#E091A9]/20 transition-colors" id="resize-right" title="Drag to resize"></div>
    <div id="sidebar-right" class="shrink-0 overflow-hidden fixed inset-y-0 right-0 z-40 w-72 translate-x-full transition-transform duration-200 md:static md:translate-x-0 md:transition-none md:w-0">
        <x-chat.right-sidebar />
    </div>

    <div id="media-modal" class="hidden fixed inset-0 z-50 bg-black/30 backdrop-blur-xl items-center justify-center p-4" onclick="closeMediaModal()">
        <button type="button" onclick="closeMediaModal()"
                class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white/60 hover:text-white hover:bg-white/20 transition-colors cursor-pointer">
            <x-icons.x class="w-4 h-4" />
        </button>
        <div id="media-modal-content" class="w-full max-w-5xl flex items-center justify-center" onclick="event.stopPropagation()">
            <img id="media-modal-image" src="" alt="Media preview" class="hidden w-full max-h-[90vh] object-contain rounded-lg">
            <video id="media-modal-video" src="" controls class="hidden w-full max-h-[90vh] rounded-lg bg-black"></video>
        </div>
    </div>
    <x-modal id="bio-modal" title="About" maxWidth="max-w-sm">
        <p id="bio-modal-text" class="text-xs text-white/70 leading-relaxed whitespace-pre-wrap"></p>
    </x-modal>
</div>
@endsection
