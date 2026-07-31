@props(['name', 'placeholder'])

<div class="relative">
    <input type="password" name="{{ $name }}" id="{{ $name }}"
           placeholder="{{ $placeholder }}"
           {{ $attributes->merge(['class' => 'w-full px-3 py-2.5 pr-9 bg-white/3 border border-white/6 text-xs text-white placeholder-white/20 rounded-sm focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200']) }}>
    <button type="button" class="password-toggle absolute right-2.5 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors cursor-pointer" title="Show password">
        <x-icons.eye class="icon-eye w-3.5 h-3.5" />
        <x-icons.eye-off class="icon-eye-off w-3.5 h-3.5 hidden" />
    </button>
</div>
