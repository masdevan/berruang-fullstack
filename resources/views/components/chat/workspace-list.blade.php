@props(['workspaces' => collect()])
@foreach ($workspaces as $workspace)
    @if (($workspace->pivot->status ?? 'member') === 'pending')
        <div data-workspace="{{ $workspace->code }}" class="flex items-center gap-2.5 px-3 py-2.5">
            @if ($workspace->avatar)
                <img src="{{ $workspace->avatarPreviewUrl() }}" alt="" class="shrink-0 w-9 h-9 rounded-full object-cover opacity-60">
            @else
                <div class="shrink-0 w-9 h-9 rounded-full bg-[#E091A9]/10 flex items-center justify-center text-xs font-medium text-[#E091A9]/60">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($workspace->name, 0, 1)) }}</div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium truncate text-white/70">{{ $workspace->name }}</p>
                    <p class="text-[9px] text-[#E091A9]/70 shrink-0 ml-2">{{ $workspace->pivot->inviter?->name ?? 'Someone' }} invited you</p>
                </div>
                <div class="flex items-center gap-1.5 mt-1.5">
                    <button type="button" onclick="confirmWorkspaceInvite('{{ $workspace->code }}', false)" class="px-2.5 py-1 rounded-sm bg-white/5 hover:bg-white/10 text-[10px] font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Reject</button>
                    <button type="button" onclick="confirmWorkspaceInvite('{{ $workspace->code }}', true)" class="px-2.5 py-1 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[10px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Accept</button>
                </div>
            </div>
        </div>
    @else
        <div data-workspace="{{ $workspace->code }}" data-name="{{ addslashes($workspace->name) }}" data-my-role="{{ $workspace->pivot->role ?? 'user' }}" data-bio="{{ addslashes($workspace->bio ?? '') }}" data-avatar="{{ $workspace->avatar ? $workspace->avatarPreviewUrl() : '' }}" data-full-avatar="{{ $workspace->avatarFullUrl() }}" data-created="{{ $workspace->created_at->format('d M Y') }}" onclick="openWorkspace(this, '{{ addslashes($workspace->name) }}', '{{ $workspace->code }}', '{{ $workspace->created_at->format('d M Y') }}')" class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer transition-all duration-150 hover:bg-white/5">
            @if ($workspace->avatar)
                <img src="{{ $workspace->avatarPreviewUrl() }}" alt="" class="shrink-0 w-9 h-9 rounded-full object-cover">
            @else
                <div class="shrink-0 w-9 h-9 rounded-full bg-[#E091A9]/15 flex items-center justify-center text-xs font-medium text-[#E091A9]">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($workspace->name, 0, 1)) }}</div>
            @endif
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
    @endif
@endforeach
