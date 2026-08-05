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
    @stack('scripts')
</body>
</html>