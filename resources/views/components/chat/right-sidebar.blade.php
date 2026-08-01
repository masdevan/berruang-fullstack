<div id="rightbar-root" class="h-full border-l border-white/6 bg-[#0F0F0F] relative overflow-hidden flex flex-col">
    <div class="flex-1 overflow-hidden flex flex-col">
        <div class="p-4 text-center border-b border-white/6">
            <div class="relative w-12 h-12 rounded-full bg-white/8 mx-auto flex items-center justify-center text-sm font-medium text-white/60"><span id="rightbar-avatar">AP</span>
                <div id="rightbar-online-dot" class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#0F0F0F] bg-white/20"></div>
            </div>
            <p id="rightbar-custom-name" class="text-xs font-medium mt-1.5 text-[#E091A9] hidden"></p>
            <p id="rightbar-real-name" class="text-xs font-medium mt-1.5 text-white/80 hidden"></p>
            <p id="rightbar-username" class="text-[10px] text-white/40 mt-0.5 hidden"></p>
        </div>

        <div id="rightbar-about" class="p-3 border-b border-white/6">
            <x-chat.section-label title="About" info="Personal information of this contact." />
            <p id="rightbar-about-text" onclick="openBioModal()" title="Click to read more" class="text-[11px] text-white/60 leading-relaxed cursor-pointer hover:text-white/80 transition-colors" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden"></p>
        </div>

        <div class="p-3 border-b border-white/6">
            <x-chat.section-label title="Shared Media" info="Images and videos shared in this conversation.">
                <span class="text-[10px] text-white/25">12 files</span>
            </x-chat.section-label>
            <div class="grid grid-cols-3 gap-1">
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/10/800/800')">
                    <img src="https://picsum.photos/id/10/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/20/800/800')">
                    <img src="https://picsum.photos/id/20/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/30/800/800')">
                    <img src="https://picsum.photos/id/30/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/40/800/800')">
                    <img src="https://picsum.photos/id/40/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="relative aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://www.w3schools.com/html/mov_bbb.mp4', 'video')">
                    <img src="https://picsum.photos/id/50/200/200" alt="Video" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors duration-200 group-hover:bg-black/40">
                        <x-icons.play class="w-8 h-8 text-white/80" />
                    </div>
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden flex items-center justify-center text-[10px] text-white/35 cursor-pointer hover:bg-white/10 hover:text-white/60 transition-colors group" onclick="openMediaGallery()">
                    +6
                </div>
            </div>
        </div>

        <div class="px-3 pt-3 pb-0 flex-1 overflow-hidden flex flex-col min-h-0">
            <x-chat.section-label title="Shared Files" info="Documents and files shared in this conversation.">
                <button type="button" onclick="openFilesGallery()" class="text-[10px] font-medium text-[#E091A9]/70 hover:text-[#E091A9] transition-colors cursor-pointer">View all</button>
            </x-chat.section-label>
            <div class="space-y-1 overflow-y-auto pr-1 pb-3 flex-1 min-h-0">
                <x-chat.file-item icon="file-doc" name="Design-System.pdf" size="2.4 MB" />
                <x-chat.file-item icon="file-image" name="mockups-v3.png" size="1.1 MB" />
                <x-chat.file-item icon="file-doc" name="Brand-Guidelines.pdf" size="3.7 MB" />
                <x-chat.file-item icon="file-image" name="hero-illustration.png" size="845 KB" />
                <x-chat.file-item icon="file-image" name="onboarding-flow.png" size="1.4 MB" />
                <x-chat.file-item icon="file-image" name="app-icon-final.png" size="312 KB" />
                <x-chat.file-item icon="file-video" name="product-demo.mp4" size="28.6 MB" />
                <x-chat.file-item icon="file-doc" name="research-summary.docx" size="1.8 MB" />
                <x-chat.file-item icon="file-archive" name="assets-bundle.zip" size="42.1 MB" />
                <x-chat.file-item icon="file-doc" name="meeting-notes.pdf" size="620 KB" />
                <x-chat.file-item icon="file-video" name="walkthrough.mp4" size="15.2 MB" />
                <x-chat.file-item icon="file-doc" name="style-guide.pdf" size="4.2 MB" />
                <x-chat.file-item icon="file-image" name="dashboard-wireframes.png" size="2.0 MB" />
                <x-chat.file-item icon="file-archive" name="icons-pack.zip" size="8.9 MB" />
                <x-chat.file-item icon="file-doc" name="user-flow-map.docx" size="956 KB" />
                <x-chat.file-item icon="file-video" name="sprint-review.mp4" size="34.7 MB" />
            </div>
        </div>
    </div>

    <div id="media-gallery" class="hidden absolute inset-0 z-10 bg-[#0F0F0F] flex-col">
        <div class="flex items-center gap-2 px-4 h-13.25 border-b border-white/6">
            <button type="button" onclick="closeMediaGallery()"
                    class="text-white/40 hover:text-white transition-colors cursor-pointer shrink-0" title="Back">
                <x-icons.chevron-left class="w-4 h-4" />
            </button>
            <p class="text-xs font-medium">Shared Media</p>
            <span id="media-count" class="text-[10px] text-white/25 ml-auto">30 files</span>
        </div>
        <div class="flex-1 overflow-y-auto p-3">
            <div id="media-gallery-grid" class="grid grid-cols-3 gap-1">
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/10/800/800')">
                    <img src="https://picsum.photos/id/10/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/20/800/800')">
                    <img src="https://picsum.photos/id/20/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/30/800/800')">
                    <img src="https://picsum.photos/id/30/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/40/800/800')">
                    <img src="https://picsum.photos/id/40/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="relative aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://www.w3schools.com/html/mov_bbb.mp4', 'video')">
                    <img src="https://picsum.photos/id/50/200/200" alt="Video" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors duration-200 group-hover:bg-black/40">
                        <x-icons.play class="w-8 h-8 text-white/80" />
                    </div>
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/60/800/800')">
                    <img src="https://picsum.photos/id/60/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/70/800/800')">
                    <img src="https://picsum.photos/id/70/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="relative aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://www.w3schools.com/html/movie.mp4', 'video')">
                    <img src="https://picsum.photos/id/80/200/200" alt="Video" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors duration-200 group-hover:bg-black/40">
                        <x-icons.play class="w-8 h-8 text-white/80" />
                    </div>
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/90/800/800')">
                    <img src="https://picsum.photos/id/90/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/100/800/800')">
                    <img src="https://picsum.photos/id/100/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/110/800/800')">
                    <img src="https://picsum.photos/id/110/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/120/800/800')">
                    <img src="https://picsum.photos/id/120/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/130/800/800')">
                    <img src="https://picsum.photos/id/130/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/140/800/800')">
                    <img src="https://picsum.photos/id/140/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="relative aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://www.w3schools.com/html/mov_bbb.mp4', 'video')">
                    <img src="https://picsum.photos/id/150/200/200" alt="Video" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors duration-200 group-hover:bg-black/40">
                        <x-icons.play class="w-8 h-8 text-white/80" />
                    </div>
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/160/800/800')">
                    <img src="https://picsum.photos/id/160/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/170/800/800')">
                    <img src="https://picsum.photos/id/170/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/180/800/800')">
                    <img src="https://picsum.photos/id/180/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="relative aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://www.w3schools.com/html/movie.mp4', 'video')">
                    <img src="https://picsum.photos/id/190/200/200" alt="Video" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors duration-200 group-hover:bg-black/40">
                        <x-icons.play class="w-8 h-8 text-white/80" />
                    </div>
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/200/800/800')">
                    <img src="https://picsum.photos/id/200/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/210/800/800')">
                    <img src="https://picsum.photos/id/210/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/220/800/800')">
                    <img src="https://picsum.photos/id/220/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="relative aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://www.w3schools.com/html/mov_bbb.mp4', 'video')">
                    <img src="https://picsum.photos/id/230/200/200" alt="Video" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors duration-200 group-hover:bg-black/40">
                        <x-icons.play class="w-8 h-8 text-white/80" />
                    </div>
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/240/800/800')">
                    <img src="https://picsum.photos/id/240/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/250/800/800')">
                    <img src="https://picsum.photos/id/250/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/260/800/800')">
                    <img src="https://picsum.photos/id/260/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
                <div class="relative aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://www.w3schools.com/html/movie.mp4', 'video')">
                    <img src="https://picsum.photos/id/270/200/200" alt="Video" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors duration-200 group-hover:bg-black/40">
                        <x-icons.play class="w-8 h-8 text-white/80" />
                    </div>
                </div>
                <div class="aspect-square bg-white/5 rounded overflow-hidden cursor-pointer hover:bg-white/10 transition-colors group" onclick="openMediaModal('https://picsum.photos/id/280/800/800')">
                    <img src="https://picsum.photos/id/280/200/200" alt="Media" class="w-full h-full object-cover transition-all duration-200 group-hover:blur-[1px] group-hover:brightness-75">
                </div>
            </div>
            <div id="media-gallery-sentinel" class="h-2"></div>
        </div>
    </div>

    <div id="files-gallery" class="hidden absolute inset-0 z-10 bg-[#0F0F0F] flex-col">
        <div class="flex items-center gap-2 px-4 h-13.25 border-b border-white/6">
            <button type="button" onclick="closeFilesGallery()"
                    class="text-white/40 hover:text-white transition-colors cursor-pointer shrink-0" title="Back">
                <x-icons.chevron-left class="w-4 h-4" />
            </button>
            <p class="text-xs font-medium">Shared Files</p>
            <span id="files-count" class="text-[10px] text-white/25 ml-auto">26 files</span>
        </div>
        <div class="flex-1 overflow-y-auto p-3">
            <div id="files-gallery-list" class="space-y-1">
                <x-chat.file-item icon="file-doc" name="Design-System.pdf" size="2.4 MB" />
                <x-chat.file-item icon="file-image" name="mockups-v3.png" size="1.1 MB" />
                <x-chat.file-item icon="file-doc" name="Brand-Guidelines.pdf" size="3.7 MB" />
                <x-chat.file-item icon="file-image" name="hero-illustration.png" size="845 KB" />
                <x-chat.file-item icon="file-image" name="onboarding-flow.png" size="1.4 MB" />
                <x-chat.file-item icon="file-image" name="app-icon-final.png" size="312 KB" />
                <x-chat.file-item icon="file-video" name="product-demo.mp4" size="28.6 MB" />
                <x-chat.file-item icon="file-doc" name="research-summary.docx" size="1.8 MB" />
                <x-chat.file-item icon="file-archive" name="assets-bundle.zip" size="42.1 MB" />
                <x-chat.file-item icon="file-doc" name="meeting-notes.pdf" size="620 KB" />
                <x-chat.file-item icon="file-video" name="walkthrough.mp4" size="15.2 MB" />
                <x-chat.file-item icon="file-doc" name="style-guide.pdf" size="4.2 MB" />
                <x-chat.file-item icon="file-image" name="dashboard-wireframes.png" size="2.0 MB" />
                <x-chat.file-item icon="file-archive" name="icons-pack.zip" size="8.9 MB" />
                <x-chat.file-item icon="file-doc" name="user-flow-map.docx" size="956 KB" />
                <x-chat.file-item icon="file-video" name="sprint-review.mp4" size="34.7 MB" />
                <x-chat.file-item icon="file-image" name="landing-mockup.png" size="1.7 MB" />
                <x-chat.file-item icon="file-doc" name="pitch-deck.pdf" size="6.3 MB" />
                <x-chat.file-item icon="file-archive" name="fonts-bundle.zip" size="12.4 MB" />
                <x-chat.file-item icon="file-video" name="case-study.mp4" size="22.9 MB" />
                <x-chat.file-item icon="file-doc" name="api-documentation.docx" size="2.2 MB" />
                <x-chat.file-item icon="file-image" name="user-personas.png" size="1.1 MB" />
                <x-chat.file-item icon="file-doc" name="sprint-notes.pdf" size="480 KB" />
                <x-chat.file-item icon="file-archive" name="prototype-files.zip" size="19.6 MB" />
                <div id="files-gallery-sentinel" class="h-1"></div>
            </div>
        </div>
    </div>
</div>
