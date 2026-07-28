@props(['type' => 'submit'])

<button type="{{ $type }}"
        {{ $attributes->merge(['class' => 'w-full py-2.5 px-4 bg-[#E091A9] text-[#0A0A0A] text-sm font-medium hover:bg-[#E8A8BC] active:scale-[0.98] transition-all duration-150 cursor-pointer']) }}>
    {{ $slot }}
</button>
