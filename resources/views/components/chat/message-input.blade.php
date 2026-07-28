<div class="border-t border-white/6 bg-[#0A0A0A]">
    <form class="flex items-start gap-2 px-3 py-2.5" id="chat-form">
        <button type="button" class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white/30 hover:text-white/60 transition-colors cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
        </button>
        <div class="flex-1 bg-white/3 border border-white/6 rounded-xl overflow-hidden focus-within:border-[#E091A9]/50 transition-all">
            <textarea id="message-input" placeholder="Type a message..." rows="1" class="w-full px-3 py-2 bg-transparent text-xs text-white placeholder-white/20 resize-none overflow-y-auto max-h-[120px] outline-none" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.08) transparent;"></textarea>
        </div>
        <button type="submit" class="flex-shrink-0 w-8 h-8 bg-[#E091A9] hover:bg-[#E8A8BC] rounded-full flex items-center justify-center transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed" id="send-btn">
            <svg class="w-4 h-4 text-[#0A0A0A] ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
            </svg>
        </button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('message-input');
        if (!textarea) return;
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    });
</script>