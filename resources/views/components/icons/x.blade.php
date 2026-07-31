@props(['class' => 'w-4 h-4'])
<svg {{ $attributes->merge(['class' => $class, 'fill' => 'none', 'stroke' => 'currentColor', 'viewBox' => '0 0 24 24']) }}>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6 6 18M6 6l12 12"/>
</svg>
