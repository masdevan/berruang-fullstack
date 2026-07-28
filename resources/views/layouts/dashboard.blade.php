<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/auth.js'])
</head>
<body class="font-sans antialiased bg-[#0A0A0A] text-white">
    <div class="flex h-screen overflow-hidden">
        <aside id="sidebar" class="w-60 border-r border-white/8 flex flex-col shrink-0 overflow-hidden h-screen">
            <div class="h-[3.25rem] flex items-center px-4 border-b border-white/8 relative">
                <button onclick="toggleWorkspaceSwitcher(event)" class="flex items-center gap-1.5 text-sm font-semibold tracking-tight text-[#E091A9] hover:text-[#E8A8BC] transition-colors cursor-pointer">
                    BerRuang
                    <svg class="w-3 h-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div id="workspace-switcher" class="absolute left-0 top-full w-full border-t border-white/8 bg-[#0A0A0A] shadow-lg hidden z-20">
                    <button class="w-full text-left px-4 py-2.5 text-sm text-[#E091A9] bg-[#E091A9]/5 border-b border-white/8">BerRuang</button>
                    <button class="w-full text-left px-4 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/4 transition-colors cursor-pointer border-b border-white/8">Personal</button>
                    <button class="w-full text-left px-4 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/4 transition-colors cursor-pointer">Client Projects</button>
                </div>
            </div>

            <nav class="flex-1">
                <a href="/" class="flex items-center gap-3 px-3 py-2.5 text-sm transition-all duration-200 {{ request()->is('/') ? 'text-[#E091A9] bg-[#E091A9]/5' : 'text-white/40 hover:text-[#E091A9] hover:bg-[#E091A9]/5' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                    <span>Spaces</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-sm text-white/40 hover:text-[#E091A9] hover:bg-[#E091A9]/5 transition-all duration-200">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <main class="flex-1 px-4 pb-8">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('click', function (e) {
            const sw = document.getElementById('workspace-switcher');
            if (sw && !e.target.closest('#workspace-switcher') && !e.target.closest('[onclick*="toggleWorkspaceSwitcher"]')) {
                sw.classList.add('hidden');
            }
        });

        function toggleWorkspaceSwitcher(e) {
            e.stopPropagation();
            document.getElementById('workspace-switcher').classList.toggle('hidden');
        }
    </script>
</body>
</html>
