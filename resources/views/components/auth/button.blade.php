@props(['type' => 'submit'])

<button type="{{ $type }}"
        {{ $attributes->merge(['class' => 'w-full py-2.5 px-4 bg-[#E091A9] text-[#0A0A0A] text-sm font-medium hover:bg-[#E8A8BC] active:scale-[0.98] transition-all duration-150 cursor-pointer rounded-lg flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed']) }}
        onclick="event.preventDefault(); this.disabled=true; this.querySelector('.spinner').classList.remove('hidden'); this.form.submit();">
    <x-icons.spinner class="spinner hidden w-4 h-4 animate-spin" />
    {{ $slot }}
</button>
