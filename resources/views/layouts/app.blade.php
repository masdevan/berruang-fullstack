<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    @foreach (['400', '500', '600', '700'] as $weight)
        <link rel="preload" as="font" type="font/woff2" crossorigin
              href="{{ Vite::asset('node_modules/@fontsource/inter/files/inter-latin-'.$weight.'-normal.woff2') }}">
    @endforeach

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">

    @vite(['resources/css/app.css', 'resources/js/chat.js'])
</head>
<body class="font-sans antialiased bg-[#0A0A0A] text-white h-screen overflow-hidden js-loading" data-user-id="{{ auth()->id() ?? '' }}">
    <div id="top-loader" class="fixed top-0 left-0 right-0 h-0.5 z-100 pointer-events-none overflow-hidden">
        <div id="top-loader-bar" class="h-full bg-[#E091A9] rounded-r-full transition-[width] duration-700 ease-out" style="width: 0%"></div>
    </div>
    <div class="flex h-screen">
        @yield('content')
    </div>
    <x-modal id="add-user-names-modal" title="Set contact name" maxWidth="max-w-sm">
        <div class="space-y-2.5">
            <input type="text" id="add-user-first-name" placeholder="First name" autocomplete="off" data-autofocus
                   class="w-full px-3 py-2 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 rounded-lg">
            <input type="text" id="add-user-last-name" placeholder="Last name" autocomplete="off"
                   class="w-full px-3 py-2 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 rounded-lg">
        </div>
        <p id="add-user-names-error" class="hidden text-[10px] text-red-400 mt-2"></p>
        <div class="flex justify-end gap-2 mt-3">
            <button type="button" onclick="submitAddUserNames(true)" class="px-2.5 py-1.5 text-xs font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Skip</button>
            <button type="button" onclick="submitAddUserNames()" class="px-2.5 py-1.5 text-xs font-medium bg-[#E091A9] text-[#0A0A0A] rounded-lg hover:bg-[#E8A8BC] transition-colors cursor-pointer">Save</button>
        </div>
    </x-modal>
    <x-modal id="add-user-modal" title="Add user">
        <div class="relative">
            <input type="text" id="add-user-input" placeholder="Username" autocomplete="off" data-autofocus
                   class="w-full px-3 py-2 pr-8 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 rounded-lg">
            <span id="add-user-status" class="absolute right-2.5 top-1/2 -translate-y-1/2 hidden text-white/25"></span>
        </div>
        <p id="add-user-error" class="hidden text-[10px] text-red-400 mt-2"></p>
        <div class="flex justify-end gap-2 mt-3">
            <button type="button" onclick="closeModal('add-user-modal')" class="px-2.5 py-1.5 text-xs font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="submitAddUser()" class="px-2.5 py-1.5 text-xs font-medium bg-[#E091A9] text-[#0A0A0A] rounded-lg hover:bg-[#E8A8BC] transition-colors cursor-pointer">Add</button>
        </div>
    </x-modal>
    <x-modal id="create-workspace-modal" title="Create workspace">
        <div class="relative">
            <input type="text" id="workspace-name-input" placeholder="Workspace name" autocomplete="off" data-autofocus maxlength="100"
                   class="w-full px-3 py-2 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 rounded-lg">
        </div>
        <p id="create-workspace-error" class="hidden text-[10px] text-red-400 mt-2"></p>
        <div class="flex justify-end gap-2 mt-3">
            <button type="button" onclick="closeModal('create-workspace-modal')" class="px-2.5 py-1.5 text-xs font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="submitCreateWorkspace()" class="px-2.5 py-1.5 text-xs font-medium bg-[#E091A9] text-[#0A0A0A] rounded-lg hover:bg-[#E8A8BC] transition-colors cursor-pointer">Create</button>
        </div>
    </x-modal>
    <x-modal id="join-workspace-modal" title="Join workspace">
        <div class="relative">
            <input type="text" id="workspace-code-input" placeholder="XXXXXXXX" autocomplete="off" data-autofocus maxlength="8"
                   oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                   class="w-full px-3 py-2 tracking-[0.3em] text-center text-sm bg-white/3 border border-white/6 text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 rounded-lg">
        </div>
        <p id="join-workspace-error" class="hidden text-[10px] text-red-400 mt-2"></p>
        <div class="flex justify-end gap-2 mt-3">
            <button type="button" onclick="closeModal('join-workspace-modal')" class="px-2.5 py-1.5 text-xs font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="submitJoinWorkspace()" class="px-2.5 py-1.5 text-xs font-medium bg-[#E091A9] text-[#0A0A0A] rounded-lg hover:bg-[#E8A8BC] transition-colors cursor-pointer">Join</button>
        </div>
    </x-modal>
    <x-modal id="add-workspace-member-modal" title="Add member">
        <div class="relative">
            <input type="text" id="add-workspace-member-input" placeholder="Username or email" autocomplete="off" data-autofocus
                   class="w-full px-3 py-2 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 rounded-lg">
        </div>
        <p id="add-workspace-member-error" class="hidden text-[10px] text-red-400 mt-2"></p>
        <div class="flex justify-end gap-2 mt-3">
            <button type="button" onclick="closeModal('add-workspace-member-modal')" class="px-2.5 py-1.5 text-xs font-medium text-white/60 hover:text-white transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="submitAddWorkspaceMember()" class="px-2.5 py-1.5 text-xs font-medium bg-[#E091A9] text-[#0A0A0A] rounded-lg hover:bg-[#E8A8BC] transition-colors cursor-pointer">Invite</button>
        </div>
    </x-modal>
    <x-modal id="ws-code-confirm-modal" title="Change workspace code?" maxWidth="max-w-xs">
        <p class="text-[11px] text-white/50 leading-relaxed">Members will need the new code to join this workspace. Continue?</p>
        <div class="flex gap-2 mt-4">
            <button type="button" onclick="closeModal('ws-code-confirm-modal')" class="flex-1 py-1.5 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="confirmWorkspaceCodeChange()" class="flex-1 py-1.5 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Continue</button>
        </div>
    </x-modal>
    <x-modal id="ws-invite-confirm-modal" title="Workspace invitation" maxWidth="max-w-xs">
        <p id="ws-invite-confirm-text" class="text-[11px] text-white/50 leading-relaxed"></p>
        <div class="flex gap-2 mt-4">
            <button type="button" onclick="closeModal('ws-invite-confirm-modal')" class="flex-1 py-1.5 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="confirmWorkspaceInviteAction()" class="flex-1 py-1.5 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Continue</button>
        </div>
    </x-modal>
    <x-modal id="ws-member-action-modal" title="Confirm" maxWidth="max-w-xs">
        <p id="ws-member-action-text" class="text-[11px] text-white/50 leading-relaxed"></p>
        <div class="flex gap-2 mt-4">
            <button type="button" id="ws-member-action-cancel" onclick="closeBulkKickConfirm()" class="flex-1 py-1.5 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
            <button type="button" onclick="runMemberAction()" class="flex-1 py-1.5 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Continue</button>
        </div>
    </x-modal>
    <x-modal id="ws-leave-delegate-modal" title="Delegate ownership" maxWidth="max-w-sm">
        <p class="text-[11px] text-white/50 leading-relaxed mb-3">You are the creator of this workspace. Choose a member to receive ownership before you leave.</p>
        <div id="ws-leave-delegate-list" class="max-h-60 overflow-y-auto space-y-1"></div>
        <div class="flex gap-2 mt-4">
            <button type="button" onclick="closeModal('ws-leave-delegate-modal')" class="flex-1 py-1.5 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
            <button type="button" id="ws-leave-delegate-confirm" onclick="confirmLeaveDelegation()" class="flex-1 py-1.5 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Leave &amp; delegate</button>
        </div>
    </x-modal>
    <x-modal id="member-profile-modal" title="Member profile" maxWidth="max-w-xs">
        <div class="flex flex-col items-center text-center">
            <div class="relative w-16 h-16 rounded-full bg-white/8 overflow-hidden flex items-center justify-center">
                <span id="mp-avatar-fallback" class="text-lg font-medium text-white/60"></span>
                <img id="mp-avatar" src="" alt="" class="hidden w-full h-full object-cover">
            </div>
<p id="mp-name" class="text-sm font-medium mt-2.5"></p>
            <p id="mp-username" class="text-[11px] text-white/40 mt-0.5"></p>
            <span id="mp-role" class="mt-1.5 text-[9px] font-medium rounded-full px-2 py-0.5"></span>
            <button type="button" id="mp-direct-chat" onclick="directChatWithMember()" class="mt-3 w-full py-1.5 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Direct chat</button>
        </div>
        <div class="mt-4 pt-3 border-t border-white/6">
            <p class="text-[10px] font-medium text-white/35 uppercase tracking-wider mb-1.5">About</p>
            <p id="mp-bio" class="text-[11px] text-white/60 leading-relaxed whitespace-pre-wrap"></p>
        </div>
        <div class="mt-3 pt-3 border-t border-white/6 flex items-center justify-between">
            <p class="text-[10px] font-medium text-white/35 uppercase tracking-wider">Joined</p>
            <p id="mp-joined" class="text-[11px] text-white/60"></p>
        </div>
    </x-modal>
    <div id="ws-member-context-menu" class="hidden fixed z-50 w-40 flex-col bg-[#1A1A1A] border border-white/10 rounded-lg p-1 shadow-xl"></div>
    <x-avatar-picker />
    @stack('scripts')
</body>
</html>