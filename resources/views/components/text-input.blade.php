@props(['name', 'type' => 'text', 'placeholder' => '', 'value' => ''])

<div class="relative">
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
           placeholder="{{ $placeholder }}" value="{{ $value }}"
           {{ $attributes->merge(['class' => 'w-full px-3 py-2.5 bg-white/3 border border-white/6 text-xs text-white placeholder-white/20 rounded-sm focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200']) }}>
</div>
