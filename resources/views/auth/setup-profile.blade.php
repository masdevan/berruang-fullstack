@extends('layouts.auth')

@section('title', 'Set Up Profile')

@section('brand')@endsection

@section('content')
    <form method="POST" action="{{ route('setup-profile.store') }}" enctype="multipart/form-data" class="space-y-5" id="setup-profile-form" data-has-avatar="{{ auth()->user()->avatar ? '1' : '0' }}">
        @csrf
        <div class="flex flex-col items-center gap-3">
            <div class="relative">
                <img id="setup-avatar-preview" src="{{ auth()->user()->avatarUrl(128) }}" alt="Profile" class="w-20 h-20 rounded-full object-cover ring-2 ring-white/10">
                <button type="button" onclick="openAvatarModal()" class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[#E091A9] flex items-center justify-center cursor-pointer hover:bg-[#E8A8BC] transition-colors" title="Add photo">
                    <x-icons.camera class="w-3 h-3 text-[#0A0A0A]" />
                </button>
            </div>
            <input type="file" id="setup-avatar-input" name="avatar" accept="image/*" class="hidden">
            <p class="text-[11px] text-white/30 -mt-1">Tap the camera to add a photo</p>
            @error('avatar')
                <p class="text-xs text-red-400/80">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="relative">
                <textarea name="bio" id="setup-bio" rows="3" placeholder="About (bio)" maxlength="500" class="w-full px-3.5 py-2.5 pb-6 bg-white/3 border border-white/6 text-sm text-white placeholder-white/20 focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200 rounded-lg resize-none">{{ old('bio') }}</textarea>
                <span id="setup-bio-count" class="absolute bottom-2.5 right-3 text-[10px] text-white/25 pointer-events-none">0/500</span>
            </div>
            @error('bio')
                <p class="text-xs text-red-400/80 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" name="skip" value="1" class="flex-1 py-2.5 rounded-lg border border-white/6 text-sm text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Skip for now</button>
            <button type="submit" id="setup-continue-btn" disabled class="flex-1 py-2.5 rounded-lg bg-[#E091A9] text-[#0A0A0A] text-sm font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-[#E091A9]">Continue</button>
        </div>
    </form>

    <x-avatar-picker form-id="setup-profile-form" />

    @push('scripts')
        @vite('resources/js/setup-profile.js')
    @endpush
@endsection
