@props(['workspaces' => collect()])
@foreach ($workspaces as $workspace)
    <div data-workspace="{{ $workspace->code }}" onclick="openWorkspace(this, '{{ addslashes($workspace->name) }}', '{{ $workspace->code }}')" class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer transition-all duration-150 hover:bg-white/5">
        <div class="shrink-0 w-9 h-9 rounded-full bg-[#E091A9]/15 flex items-center justify-center text-xs font-medium text-[#E091A9]">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($workspace->name, 0, 1)) }}</div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium truncate text-white/80">{{ $workspace->name }}</p>
                <p class="text-[10px] text-white/30 shrink-0 ml-2">{{ $workspace->created_at->format('H:i') }}</p>
            </div>
            <div class="flex items-center justify-between mt-0.5">
                <p class="text-[11px] text-white/35 truncate">Code: <span class="tracking-widest text-white/50">{{ $workspace->code }}</span></p>
            </div>
        </div>
    </div>
@endforeach
