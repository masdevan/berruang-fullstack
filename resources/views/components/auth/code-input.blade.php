@props([])

<div class="flex gap-2 sm:gap-2.5 justify-center" id="code-inputs">
    @for ($i = 0; $i < 6; $i++)
        <input type="text" name="code[]" id="code-{{ $i }}" maxlength="1" inputmode="numeric"
               autocomplete="one-time-code"
                class="flex-1 min-w-0 h-13 text-center text-sm sm:text-base bg-white/3 border border-white/6 text-white focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200 rounded-lg"
               oninput="handleCodeInput(this, {{ $i }})"
               onkeydown="handleCodeKeydown(event, {{ $i }})"
               onpaste="handleCodePaste(event)">
    @endfor
</div>
