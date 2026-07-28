@extends('layouts.app')

@section('title', 'Messages')

@section('content')
    <div id="sidebar-left" class="flex-shrink-0 overflow-hidden" style="width: 320px;">
        <x-chat.conversation-list />
    </div>
    <div class="w-1 flex-shrink-0 cursor-col-resize bg-transparent hover:bg-[#E091A9]/20 transition-colors" id="resize-left" title="Drag to resize"></div>
    <x-chat.message-area />
    <div class="w-1 flex-shrink-0 cursor-col-resize bg-transparent hover:bg-[#E091A9]/20 transition-colors" id="resize-right" title="Drag to resize"></div>
    <div id="sidebar-right" class="flex-shrink-0 overflow-hidden" style="width: 288px;">
        <x-chat.right-sidebar />
    </div>
@endsection