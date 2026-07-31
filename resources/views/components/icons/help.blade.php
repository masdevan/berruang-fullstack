@props(['class' => 'w-3.5 h-3.5'])
<svg {{ $attributes->merge(['class' => $class, 'fill' => 'none', 'stroke' => 'currentColor', 'viewBox' => '0 0 24 24']) }}>
    <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/>
    <path stroke-linecap="round" stroke-width="1.5" d="M12 17h.01"/>
</svg>
