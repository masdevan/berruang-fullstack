@props(['name', 'avatar', 'hasAvatar' => false, 'custom' => false, 'lastMessage' => '', 'time' => '', 'unread' => 0, 'online' => false, 'active' => false, 'about' => '', 'realName' => '', 'username' => '', 'customName' => '', 'userId' => '', 'draft' => ''])

<div data-conversation="{{ strtolower($name) }} {{ strtolower($lastMessage) }}" data-last-message="{{ $lastMessage }}" data-user-id="{{ $userId }}" data-name="{{ $name }}" data-avatar="{{ $avatar }}" data-has-avatar="{{ $hasAvatar ? '1' : '0' }}" data-status="{{ $online ? 'online' : 'offline' }}" data-about="{{ $about }}" data-real-name="{{ $realName }}" data-username="{{ $username }}" data-custom-name="{{ $customName }}" onclick="openConversation(this.dataset.name, this.dataset.avatar, this.dataset.status, this.dataset.about, this.dataset.customName, this.dataset.realName, this.dataset.username, this.dataset.hasAvatar === '1')" class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer transition-all duration-150 hover:bg-white/5 {{ $active ? 'bg-white/5' : '' }}">
    <div class="relative shrink-0">
        @if ($hasAvatar)
            <img src="{{ $avatar }}" alt="{{ $name }}" class="w-9 h-9 rounded-full object-cover">
        @else
            <div class="w-9 h-9 rounded-full bg-white/8 flex items-center justify-center text-[10px] font-medium text-white/60">
                {{ $avatar }}
            </div>
        @endif
        <div class="online-dot absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-[#0F0F0F] {{ $online ? 'bg-green-500' : 'bg-white/20' }}"></div>
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
            <p class="flex items-center gap-1.5 min-w-0 text-xs font-medium truncate {{ $active ? 'text-white' : 'text-white/80' }}">
                <span class="truncate">{{ $customName ? $name : '@'.$username }}</span>
                @if (! $custom)
                    <span class="shrink-0 text-[8px] font-medium text-white/35 bg-white/8 rounded-full px-1.5 py-0.5">unsaved</span>
                @endif
            </p>
            <p class="text-[10px] text-white/30 shrink-0 ml-2">{{ $time }}</p>
        </div>
        <div class="flex items-center justify-between mt-0.5">
            <p class="conversation-last text-[11px] truncate {{ $draft ? 'text-[#E091A9]/80' : 'text-white/35' }}">{{ $draft ? 'Draft: '.$draft : $lastMessage }}</p>
            @if ($unread)
                <span class="unread-badge shrink-0 ml-2 min-w-3.75 h-3.75 rounded-full bg-[#E091A9] text-[#0A0A0A] text-[7px] font-semibold flex items-center justify-center px-1 leading-none">{{ $unread }}</span>
            @endif
        </div>
    </div>
</div>
