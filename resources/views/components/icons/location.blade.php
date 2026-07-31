@props(['class' => 'w-3 h-3'])
<svg {{ $attributes->merge(['class' => $class, 'fill' => 'none', 'stroke' => 'currentColor', 'viewBox' => '0 0 24 24']) }}>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/>
    <circle cx="12" cy="10" r="3" stroke-width="1.5"/>
</svg>
