@props(['class' => 'w-8 h-8'])
<svg {{ $attributes->merge(['class' => $class, 'fill' => 'currentColor', 'viewBox' => '0 0 24 24']) }}>
    <circle cx="12" cy="12" r="10" fill="rgba(0,0,0,0.45)"/>
    <path d="M10 8l6 4-6 4V8z"/>
</svg>
