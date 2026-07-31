@props(['id', 'title' => '', 'maxWidth' => 'max-w-xs'])

<div id="{{ $id }}" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="closeModal('{{ $id }}')">
    <div class="w-full {{ $maxWidth }} bg-[#1A1A1A]/80 backdrop-blur-xl border border-white/10 rounded-lg p-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium">{{ $title }}</p>
            <button type="button" onclick="closeModal('{{ $id }}')" class="text-white/40 hover:text-white transition-colors cursor-pointer" title="Close">
                <x-icons.x class="w-4 h-4" />
            </button>
        </div>
        {{ $slot }}
    </div>
</div>
