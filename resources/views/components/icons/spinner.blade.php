@props(['class' => 'w-4 h-4'])
<svg {{ $attributes->merge(['class' => $class, 'fill' => 'none', 'viewBox' => '0 0 24 24']) }}>
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
</svg>
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .animate-spin { animation: spin 0.6s linear infinite; }
</style>
