@extends('layouts.dashboard')

@section('title', 'Spaces')

@section('content')
    <div class="flex items-center justify-between pt-4 pb-3">
        <h1 class="text-lg font-semibold">Spaces</h1>
        <button onclick="openModal()" class="text-sm text-white/70 hover:text-[#E091A9] bg-white/8 hover:bg-[#E091A9]/10 px-3 py-1.5 transition-all duration-200 cursor-pointer">+ Add Workspace</button>
    </div>

    <div id="workspace-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-full max-w-sm border border-white/8 bg-[#0A0A0A] p-6 pointer-events-auto">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-sm font-semibold">New Workspace</h2>
                    <button onclick="closeModal()" class="text-white/30 hover:text-white/60 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form>
                    <label class="block text-xs text-white/40 mb-1.5">Workspace name</label>
                    <input type="text" class="w-full border border-white/8 bg-white/2 px-3 py-2 text-sm text-white outline-none transition-colors duration-200 focus:border-[#E091A9]/40" placeholder="e.g. Design Team">
                    <div class="flex justify-end gap-3 mt-5">
                        <button type="button" onclick="closeModal()" class="text-xs text-white/40 hover:text-white/70 px-3 py-1.5 transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="text-xs text-white bg-[#E091A9] hover:bg-[#E8A8BC] px-4 py-1.5 transition-colors cursor-pointer">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-4">
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/10/400/200" alt="Design Team" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Design Team</p>
                <p class="text-xs text-white/30 mt-0.5">12 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/20/400/200" alt="Development" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Development</p>
                <p class="text-xs text-white/30 mt-0.5">8 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/30/400/200" alt="Marketing" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Marketing</p>
                <p class="text-xs text-white/30 mt-0.5">5 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/40/400/200" alt="Research" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Research</p>
                <p class="text-xs text-white/30 mt-0.5">3 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/50/400/200" alt="Product" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Product</p>
                <p class="text-xs text-white/30 mt-0.5">15 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/60/400/200" alt="Engineering" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Engineering</p>
                <p class="text-xs text-white/30 mt-0.5">20 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/70/400/200" alt="Finance" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Finance</p>
                <p class="text-xs text-white/30 mt-0.5">4 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/80/400/200" alt="HR" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">HR</p>
                <p class="text-xs text-white/30 mt-0.5">6 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/90/400/200" alt="Creative" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Creative</p>
                <p class="text-xs text-white/30 mt-0.5">7 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/100/400/200" alt="Operations" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Operations</p>
                <p class="text-xs text-white/30 mt-0.5">9 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/110/400/200" alt="Legal" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Legal</p>
                <p class="text-xs text-white/30 mt-0.5">2 members</p>
            </div>
        </div>
        <div class="border border-white/8 bg-white/2 hover:bg-white/4 hover:border-white/12 transition-all duration-200 cursor-pointer">
            <img src="https://picsum.photos/id/120/400/200" alt="Data" class="w-full aspect-[21/9] object-cover border-b border-white/8">
            <div class="p-4 pt-3">
                <p class="text-sm font-medium">Data</p>
                <p class="text-xs text-white/30 mt-0.5">11 members</p>
            </div>
        </div>
    </div>
@endsection

<script>
    function openModal() { document.getElementById('workspace-modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('workspace-modal').classList.add('hidden'); }
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
</script>
