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
    <script>
        function toggleLeft() {
            const el = document.getElementById('sidebar-left');
            el.style.transition = 'width 0.2s';
            el.style.width = el.style.width === '0px' ? '320px' : '0px';
            setTimeout(() => el.style.transition = '', 200);
        }
        function toggleRight() {
            const el = document.getElementById('sidebar-right');
            el.style.transition = 'width 0.2s';
            el.style.width = el.style.width === '0px' ? '288px' : '0px';
            setTimeout(() => el.style.transition = '', 200);
        }

        function makeResizable(id, handleId, minWidth, maxWidth) {
            const el = document.getElementById(id);
            const handle = document.getElementById(handleId);
            let startX, startWidth;

            handle.addEventListener('mousedown', function (e) {
                startX = e.clientX;
                startWidth = el.offsetWidth;
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });

            function onMove(e) {
                const delta = e.clientX - startX;
                let newWidth = id === 'sidebar-left' ? startWidth + delta : startWidth - delta;
                newWidth = Math.max(minWidth, Math.min(maxWidth, newWidth));
                el.style.width = newWidth + 'px';
            }

            function onUp() {
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            }
        }

        makeResizable('sidebar-left', 'resize-left', 200, 500);
        makeResizable('sidebar-right', 'resize-right', 200, 400);
    </script>
@endsection