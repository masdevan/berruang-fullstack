@props(['class' => 'w-4 h-4'])
<svg {{ $attributes->merge(['class' => $class, 'fill' => 'currentColor', 'viewBox' => '0 0 24 24']) }}>
    <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
    <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
    <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
</svg>
