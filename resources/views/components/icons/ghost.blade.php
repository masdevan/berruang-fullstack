@props(['class' => 'w-4 h-4'])
<svg {{ $attributes->merge(['class' => $class, 'fill' => 'none', 'stroke' => 'currentColor', 'viewBox' => '0 0 24 24']) }}>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 9h.01"/>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 9h.01"/>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 1a8 8 0 0 0-8 8v12l3-3 2.5 2.5L12 17.5l2.5 2.5L17 18l3 3V9a8 8 0 0 0-8-8z"/>
</svg>
