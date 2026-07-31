@props(['type' => 'text', 'name', 'placeholder', 'autofocus' => false])

@if ($type === 'password')
    <div class="relative">
        <input type="password" name="{{ $name }}" id="{{ $name }}"
               placeholder="{{ $placeholder }}"
               {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 pr-10 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200 rounded-lg']) }}>
        <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors cursor-pointer" title="Show password">
            <x-icons.eye class="icon-eye w-4 h-4" />
            <x-icons.eye-off class="icon-eye-off w-4 h-4 hidden" />
        </button>
    </div>
@else
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
           placeholder="{{ $placeholder }}"
           value="{{ old($name) }}"
           {{ $autofocus ? 'autofocus' : '' }}
           {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200 rounded-lg']) }}>
@endif
