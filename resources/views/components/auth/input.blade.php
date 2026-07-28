@props(['type' => 'text', 'name', 'placeholder', 'autofocus' => false])

<input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
       placeholder="{{ $placeholder }}"
       value="{{ old($name) }}"
       {{ $autofocus ? 'autofocus' : '' }}
       {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200 rounded-lg']) }}>
