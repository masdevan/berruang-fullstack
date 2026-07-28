<div class="h-full border-r border-white/6 flex flex-col bg-[#0F0F0F]">
    <div class="flex items-center justify-between px-4 py-3 border-b border-white/6">
        <img src="{{ asset('logo.png') }}" alt="BerRuang" class="h-7">
        <div class="flex items-center gap-2">
            <button onclick="toggleSearch()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer" title="Search">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </button>
            <a href="#" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer" title="Settings">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
        </div>
    </div>
    <div id="search-bar" class="p-3 border-b border-white/6 hidden">
        <div class="relative">
            <input type="text" id="search-input" placeholder="Search conversations..." class="w-full pl-3 pr-9 py-2 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 rounded-lg focus:outline-none focus:border-[#E091A9]/50 transition-all">
            <button type="button" onclick="searchConversations()" class="absolute right-2 top-1/2 -translate-y-1/2 text-white/20 hover:text-white/60 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </button>
        </div>
    </div>
    <script>
        function toggleSearch() {
            const bar = document.getElementById('search-bar');
            const input = document.getElementById('search-input');
            bar.classList.toggle('hidden');
            if (!bar.classList.contains('hidden')) input.focus();
        }
        function searchConversations() {
            const input = document.getElementById('search-input');
            input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter' }));
        }
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('search-input');
            if (!input) return;
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    const val = this.value.toLowerCase().trim();
                    document.querySelectorAll('[data-conversation]').forEach(el => {
                        el.style.display = val ? (el.dataset.conversation.includes(val) ? '' : 'none') : '';
                    });
                }
            });
        });
    </script>

    <div class="flex-1 overflow-y-auto">
        <x-chat.conversation-item
            name="Alya Putri"
            avatar="AP"
            last-message="Okay, I'll review the design first thing tomorrow!"
            time="5m"
            online="true"
            active="true" />

        <x-chat.conversation-item
            name="Design Team"
            avatar="DT"
            last-message="Rama: New mockups are ready for feedback"
            time="1h"
            unread="3" />

        <x-chat.conversation-item
            name="Rama Wijaya"
            avatar="RW"
            last-message="Sounds good, let's finalize it by Friday"
            time="2h"
            online="true" />

        <x-chat.conversation-item
            name="Sari Dewi"
            avatar="SD"
            last-message="Thank you for the quick response!"
            time="3h" />

        <x-chat.conversation-item
            name="Budi Santoso"
            avatar="BS"
            last-message="Can you send me the file?"
            time="Yesterday" />

        <x-chat.conversation-item
            name="Marketing Team"
            avatar="MT"
            last-message="Doni: Campaign results are in"
            time="Yesterday"
            unread="7" />

        <x-chat.conversation-item
            name="Doni Prasetyo"
            avatar="DP"
            last-message="Let's discuss this in the meeting"
            time="2d" />
    </div>
</div>