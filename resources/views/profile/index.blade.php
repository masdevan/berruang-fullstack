@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div id="sidebar-left" class="shrink-0 overflow-hidden w-full md:w-80 md:block">
        <x-chat.conversation-list />
    </div>
    <div class="hidden md:block w-1 shrink-0 cursor-col-resize bg-transparent hover:bg-[#E091A9]/20 transition-colors" id="resize-left" title="Drag to resize"></div>
    <div class="flex-1 flex-col hidden md:flex min-w-0">
        <div class="flex items-center gap-2 px-4 h-13.25 border-b border-white/6 bg-[#0A0A0A]">
            <button onclick="toggleLeft()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer shrink-0" title="Toggle sidebar">
                <x-icons.dots-grid />
            </button>
            <p class="text-xs font-medium">Profile</p>
        </div>
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-md mx-auto w-full px-6 py-10">
                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="flex items-center gap-4" id="avatar-form">
                    @csrf
                    <div class="relative shrink-0">
                        <div class="w-16 h-16 rounded-full overflow-hidden ring-2 ring-white/10">
                            <img id="avatar-preview" src="{{ auth()->user()->avatarUrl(64) }}" alt="Profile" class="w-full h-full object-cover">
                        </div>
                        <button type="button" onclick="openAvatarModal()" class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[#E091A9] flex items-center justify-center cursor-pointer hover:bg-[#E8A8BC] transition-colors" title="Change picture">
                            <x-icons.camera class="w-3 h-3 text-[#0A0A0A]" />
                        </button>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-white/40 mt-0.5">@<span>{{ auth()->user()->username }}</span></p>
                        @if (session('avatar_status'))
                            <p class="text-[10px] text-green-400 mt-1">{{ session('avatar_status') }}</p>
                        @endif
                        @error('avatar')
                            <p class="text-[10px] text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </form>
                @php
                    $lastChange = auth()->user()->username_changed_at;
                    $daysLeft = $lastChange ? (int) ceil(now()->diffInDays($lastChange->copy()->addDays(7), false)) : null;
                    $usernameError = $errors->first('username');
                    $usernameLocked = $usernameError && str_contains($usernameError, 'once every');
                @endphp

                <form method="POST" action="{{ route('profile.account') }}" class="mt-8" id="account-form" data-original-username="{{ auth()->user()->username }}">
                    @csrf
                    <p class="text-xs font-medium mb-3">Account</p>
                    <div class="space-y-3">
                        <div>
                            <x-text-input name="name" value="{{ old('name', auth()->user()->name) }}" placeholder="Name" required />
                            @error('name')
                                <p class="text-[10px] text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/25 text-xs pointer-events-none">@</span>
                                <x-text-input name="username" value="{{ $usernameLocked ? auth()->user()->username : old('username', auth()->user()->username) }}" required class="pl-7" />
                            </div>
                            @if ($daysLeft && $daysLeft > 0)
                                <p id="username-hint" class="text-[10px] text-yellow-400/70 mt-1 {{ $errors->has('username') ? 'hidden' : '' }}">You can change your username again in {{ $daysLeft }} day(s).</p>
                            @else
                                <p id="username-hint" class="text-[10px] text-white/25 mt-1 {{ $errors->has('username') ? 'hidden' : '' }}">Username can only be changed once every 7 days.</p>
                            @endif
                            @error('username')
                                <p id="username-error" class="text-[10px] text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div class="relative">
                                <textarea name="bio" id="bio" rows="3" placeholder="Bio" maxlength="500" class="w-full px-3 py-2.5 pb-5 bg-white/3 border border-white/6 text-xs text-white placeholder-white/20 rounded-sm focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200 resize-none">{{ old('bio', auth()->user()->bio) }}</textarea>
                                <span id="bio-count" class="absolute bottom-2.5 right-2 text-[9px] text-white/25 pointer-events-none">0/500</span>
                            </div>
                            @error('bio')
                                <p class="text-[10px] text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @if (session('account_status'))
                            <div id="account-status" class="relative overflow-hidden rounded-sm bg-green-500/10 border border-green-500/20 px-3 py-2">
                                <p class="text-[10px] text-green-400">{{ session('account_status') }}</p>
                                <div id="account-status-bar" class="absolute bottom-0 left-0 h-0.5 bg-green-400" style="width: 100%"></div>
                            </div>
                        @endif
                        <button type="submit" class="w-full py-2 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-xs font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">
                            Save changes
                        </button>
                    </div>
                </form>

                <x-avatar-picker />

                <div id="camera-modal" class="hidden fixed inset-0 z-60 items-center justify-center bg-black/70 p-4" onclick="closeCamera()">
                    <div class="w-full max-w-sm bg-[#1A1A1A] border border-white/10 rounded-sm overflow-hidden" onclick="event.stopPropagation()">
                        <div class="bg-black">
                            <video id="camera-video" autoplay playsinline muted class="w-full aspect-square object-cover"></video>
                            <canvas id="camera-canvas" class="hidden"></canvas>
                        </div>
                        <div class="flex gap-2 p-3">
                            <button type="button" onclick="closeCamera()" class="flex-1 py-2 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
                            <button type="button" onclick="captureAvatar()" class="flex-1 py-2 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Capture</button>
                        </div>
                    </div>
                </div>

                <div id="crop-modal" class="hidden fixed inset-0 z-60 items-center justify-center bg-black/70 p-4" onclick="showDiscardCropConfirm()">
                    <div class="w-full max-w-sm bg-[#1A1A1A] border border-white/10 rounded-sm overflow-hidden" onclick="event.stopPropagation()">
                        <div class="bg-black">
                            <img id="crop-image" class="block w-full max-h-80 mx-auto" alt="Crop preview">
                        </div>
                        <div class="flex gap-2 p-3">
                            <button type="button" onclick="cancelCrop()" class="flex-1 py-2 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
                            <button type="button" onclick="confirmCrop()" class="flex-1 py-2 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Save</button>
                        </div>
                    </div>
                </div>

                <div id="discard-crop-confirm" class="hidden fixed inset-0 z-60 items-center justify-center bg-black/70 p-4" onclick="hideDiscardCropConfirm()">
                    <div class="w-full max-w-xs bg-[#1A1A1A] border border-white/10 rounded-sm p-4" onclick="event.stopPropagation()">
                        <p class="text-xs font-medium">Discard changes?</p>
                        <p class="text-[11px] text-white/50 mt-1 leading-relaxed">Your cropped photo will not be saved.</p>
                        <div class="flex gap-2 mt-4">
                            <button type="button" onclick="hideDiscardCropConfirm()" class="flex-1 py-1.5 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Keep editing</button>
                            <button type="button" onclick="discardCrop()" class="flex-1 py-1.5 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Discard</button>
                        </div>
                    </div>
                </div>

                <div id="username-confirm" class="hidden fixed inset-0 z-60 items-center justify-center bg-black/70 p-4" onclick="hideUsernameConfirm()">
                    <div class="w-full max-w-xs bg-[#1A1A1A] border border-white/10 rounded-sm p-4" onclick="event.stopPropagation()">
                        <p class="text-xs font-medium">Change username?</p>
                        <p class="text-[11px] text-white/50 mt-1 leading-relaxed">Your username will be locked for 7 days after this change.</p>
                        <div class="flex gap-2 mt-4">
                            <button type="button" onclick="hideUsernameConfirm()" class="flex-1 py-1.5 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
                            <button type="button" onclick="confirmUsernameChange()" class="flex-1 py-1.5 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Continue</button>
                        </div>
                    </div>
                </div>

                <div class="mt-8 space-y-2">
                    <div class="flex items-center justify-between px-4 py-3 bg-white/3 rounded-sm">
                        <span class="text-[11px] text-white/40">Email</span>
                        <span class="text-xs text-white/80 truncate ml-4">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3 bg-white/3 rounded-sm">
                        <span class="text-[11px] text-white/40">Member since</span>
                        <span class="text-xs text-white/80">{{ auth()->user()->created_at->format('d M Y') }}</span>
                    </div>
                </div>

                <div class="mt-8">
                    <p class="text-xs font-medium mb-3">Change Password</p>
                    <form method="POST" action="{{ route('profile.password') }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-password-input name="current_password" value="{{ old('current_password') }}" placeholder="Current password" required />
                            @error('current_password')
                                <p class="text-[10px] text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-password-input name="password" placeholder="New password (min 8 characters)" required oninput="checkPasswordStrength(this.value)" />
                            <div id="password-strength" class="mt-3 hidden">
                                <div class="flex gap-1 mb-1.5">
                                    <div id="str-0" class="h-0.5 flex-1 bg-white/6 transition-all duration-300"></div>
                                    <div id="str-1" class="h-0.5 flex-1 bg-white/6 transition-all duration-300"></div>
                                    <div id="str-2" class="h-0.5 flex-1 bg-white/6 transition-all duration-300"></div>
                                </div>
                                <p id="strength-label" class="text-xs text-white/30"></p>
                            </div>
                            @error('password')
                                <p class="text-[10px] text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-password-input name="password_confirmation" placeholder="Confirm new password" required />
                        @if (session('status'))
                            <p class="text-[10px] text-green-400">{{ session('status') }}</p>
                        @endif
                        <button type="submit" class="w-full py-2 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-xs font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">
                            Update Password
                        </button>
                    </form>
                </div>

                <div class="mt-4">
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                        <button type="submit" class="w-full py-2 rounded-sm border border-white/6 text-xs text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
                            Reset Password via Email
                        </button>
                    </form>
                </div>

                <div id="logout-confirm" class="hidden fixed inset-0 z-60 items-center justify-center bg-black/70 p-4" onclick="hideLogoutConfirm()">
                    <div class="w-full max-w-xs bg-[#1A1A1A] border border-white/10 rounded-sm p-4" onclick="event.stopPropagation()">
                        <p class="text-xs font-medium">Log out?</p>
                        <p class="text-[11px] text-white/50 mt-1 leading-relaxed">You will be returned to the sign in page.</p>
                        <div class="flex gap-2 mt-4">
                            <button type="button" onclick="hideLogoutConfirm()" class="flex-1 py-1.5 rounded-sm border border-white/6 text-[11px] text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">Cancel</button>
                            <button type="button" onclick="confirmLogout()" class="flex-1 py-1.5 rounded-sm bg-[#E091A9] text-[#0A0A0A] text-[11px] font-medium hover:bg-[#E8A8BC] transition-colors cursor-pointer">Log out</button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pb-6">
                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <button type="submit" class="w-full py-2 rounded-sm border border-white/6 text-xs text-white/60 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        @vite('resources/js/profile.js')
    @endpush
@endsection
