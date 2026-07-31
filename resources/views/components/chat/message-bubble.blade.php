@props(['message', 'time', 'sender' => 'them'])

<div class="flex {{ $sender === 'me' ? 'justify-end' : 'justify-start' }} mb-2">
    <div class="max-w-[70%] {{ $sender === 'me' ? 'bg-[#E091A9]/10' : 'bg-white/5' }} rounded-sm px-3 py-1.5">
        <p class="text-xs text-white/85 leading-relaxed">{{ $message }}</p>
        <p class="text-[9px] text-white/25 text-right mt-0.5">{{ $time }}</p>
    </div>
</div>