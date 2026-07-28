@props(['type' => 'submit'])

<button type="{{ $type }}"
        {{ $attributes->merge(['class' => 'w-full py-2.5 px-4 bg-[#E091A9] text-[#0A0A0A] text-sm font-medium hover:bg-[#E8A8BC] active:scale-[0.98] transition-all duration-150 cursor-pointer rounded-lg flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed']) }}
        onclick="event.preventDefault(); this.disabled=true; this.querySelector('.spinner').classList.remove('hidden'); this.form.submit();">
    <svg class="spinner hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    {{ $slot }}
</button>

<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .animate-spin { animation: spin 0.6s linear infinite; }
</style>
