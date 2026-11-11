<x-app-layout title="Dormitory Blocks Management">
    <div class="space-y-6" x-data="{
        editModalOpen: false,
        detailModalOpen: false,
        editBlock: {
            id: null,
            name: '',
            block_code: '',
            gender_category: 'male',
            location: '',
            description: ''
        },
        detailBlock: {
            id: null,
            name: '',
            block_code: '',
            gender_category: 'male',
            location: '',
            description: '',
            rooms_count: 0,
            active_allocations_count: 0,
            image_url: ''
        },
        openEdit(block) {
            this.editBlock = {
                id: block.id,
                name: block.name,
                block_code: block.block_code,
                gender_category: block.gender_category,
                location: block.location || '',
                description: block.description || ''
            };
            this.editModalOpen = true;
        },
        openDetail(block) {
            this.detailBlock = block;
            this.detailModalOpen = true;
        }
    }">
        <!-- Register Block Form -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90 mb-1 block">Campus Infrastructure</span>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Register New Dormitory Block</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 mb-5 max-w-2xl leading-relaxed">Specify the block code and accommodation category (Male or Female).</p>

            <form method="POST" action="{{ route('admin.blocks.store') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Block Name</label>
                    <input type="text" name="name" required placeholder="e.g. Fasiledes Hall" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Block Code</label>
                    <input type="text" name="block_code" required placeholder="e.g. BLK-48" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono placeholder-slate-500 transition-all" />
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Accommodation Category</label>
                    <select name="gender_category" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                        <option value="male">Male Students Only</option>
                        <option value="female">Female Students Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Campus Location</label>
                    <input type="text" name="location" placeholder="e.g. North Wing, 3rd Gate" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                </div>
                <div class="sm:col-span-2 md:col-span-4 flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98]">
                        + Create Block
                    </button>
                </div>
            </form>
        </div>

        <!-- Search and Filter Bar -->
        <div class="relative z-20 p-4 rounded-2xl bg-[#111726]/70 border border-white/[0.06] backdrop-blur-md flex flex-col sm:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.blocks.index') }}" class="w-full flex flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:w-80">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by BLK number (e.g. BLK-45)..."
                           class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl pl-9 pr-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-xs text-slate-400 font-medium uppercase tracking-wider flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter:
                    </span>
                    <select name="gender" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                        <option value="">All Categories</option>
                        <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male Blocks Only</option>
                        <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female Blocks Only</option>
                    </select>

                    <button type="submit" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-md shadow-emerald-500/20 active:scale-[0.98]">
                        Apply
                    </button>

                    <x-block-sort-dropdown />

                    @if(request()->hasAny(['search', 'gender', 'sort']))
                        <a href="{{ route('admin.blocks.index') }}" class="px-3.5 py-2.5 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] border border-white/10 rounded-xl transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Blocks Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($blocks as $blk)
                <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] hover:border-white/[0.15] transition-all flex flex-col justify-between overflow-hidden group shadow-xl">
                    <!-- Top Section: Block Image with BLK Number, Block Name, Edit, and Delete action buttons directly on the image -->
                    <div class="relative w-full h-52 overflow-hidden bg-[#0F1420] theme-preserve-dark">
                        <img src="{{ $blk->image_url }}" alt="{{ $blk->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        
                        <!-- Scrim gradient overlay to ensure text is 100% readable -->
                        <div class="absolute inset-0 bg-gradient-to-t from-[#121927] via-[#121927]/40 to-black/60 pointer-events-none"></div>

                        <!-- Top row ON the image: BLK-number badge (Left) & Action buttons (Right) -->
                        <div class="absolute top-3 left-3 right-3 flex items-center justify-between z-10">
                            <!-- BLK-number badge -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-mono text-xs sm:text-sm font-semibold text-emerald-400 bg-[#0B0F17]/90 border border-emerald-500/30 shadow-lg backdrop-blur-md">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $blk->block_code }}
                            </span>

                            <!-- Actions directly on image: View Detail, Edit & Delete -->
                            <div class="flex items-center gap-1 bg-[#0B0F17]/80 backdrop-blur-md p-1 rounded-xl border border-white/10 shadow-lg">
                                <!-- View Detail Button -->
                                <button type="button" @click="openDetail({{ Js::from($blk) }})" title="View Details" class="p-1.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>

                                <!-- Edit Block Button -->
                                <button type="button" @click="openEdit({{ Js::from($blk) }})" title="Edit Block" class="p-1.5 text-slate-300 hover:text-emerald-400 hover:bg-white/10 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Delete Block Button -->
                                <form method="POST" action="{{ route('admin.blocks.destroy', $blk) }}" onsubmit="return confirm('Permanently delete {{ $blk->block_code }} ({{ $blk->name }})?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Block" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Bottom row ON the image: Block Name written clearly on the image -->
                        <div class="absolute bottom-3 left-3 right-3 z-10">
                            <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider block drop-shadow">Dormitory Block</span>
                            <h3 class="text-xl sm:text-2xl font-bold text-white drop-shadow-md tracking-tight truncate">{{ $blk->name }}</h3>
                        </div>
                    </div>

                    <!-- Details Area BELOW the image -->
                    <div class="p-5 space-y-4">
                        <!-- Heading row below image: BLK Code, Name, Gender Category & Location -->
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-white/[0.06]">
                            <div>
                                <span class="text-[10px] font-mono font-semibold text-emerald-400 uppercase tracking-wider">{{ $blk->block_code }}</span>
                                <h4 class="text-lg font-bold text-white tracking-tight leading-tight">{{ $blk->name }}</h4>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium uppercase tracking-wider shrink-0 {{ $blk->gender_category === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                {{ $blk->gender_category }} Block
                            </span>
                        </div>

                        <!-- Campus Location & Description -->
                        <div class="space-y-1.5">
                            <div class="flex items-center text-xs text-slate-400 gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $blk->location ?? 'Campus Residence Zone' }}</span>
                            </div>
                            @if($blk->description)
                                <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">{{ $blk->description }}</p>
                            @endif
                        </div>

                        <!-- Statistics Grid below Image -->
                        <div class="grid grid-cols-2 gap-2.5 p-3.5 rounded-xl bg-[#0F1420]/60 border border-white/[0.05] text-xs">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Rooms Configured</span>
                                <span class="font-semibold text-white text-sm mt-0.5 block font-mono">{{ $blk->rooms_count }} / 50 Rooms</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Current Residents</span>
                                <span class="font-semibold text-emerald-400 text-sm mt-0.5 block font-mono">{{ $blk->active_allocations_count }} Students</span>
                            </div>
                        </div>

                        <!-- Dedicated Action Bar: View Detail, Edit, and Delete buttons -->
                        <div class="pt-3 border-t border-white/[0.06] flex items-center justify-between gap-2">
                            <!-- View Detail Button -->
                            <button type="button" @click="openDetail({{ Js::from($blk) }})" class="px-3 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-1.5 shadow-sm">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>View Detail</span>
                            </button>

                            <div class="flex items-center gap-1.5">
                                <!-- Edit Button -->
                                <button type="button" @click="openEdit({{ Js::from($blk) }})" class="px-3 py-1.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow flex items-center gap-1 active:scale-[0.98]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </button>

                                <!-- Delete Button -->
                                <form method="POST" action="{{ route('admin.blocks.destroy', $blk) }}" onsubmit="return confirm('Permanently delete {{ $blk->block_code }} ({{ $blk->name }})?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-medium text-rose-400 hover:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 rounded-xl transition-all flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 col-span-3 rounded-3xl bg-[#121927]/80 border border-white/[0.07] text-center text-slate-400 text-xs">
                    No dormitory blocks found matching your query.
                </div>
            @endforelse
        </div>

        <!-- Blocks Pagination Links (9 blocks per page) -->
        <div class="pt-2">
            {{ $blocks->links() }}
        </div>

        <!-- ========================================== -->
        <!-- MODAL: View Block Details                  -->
        <!-- ========================================== -->
        <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="detailModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/[0.08] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- Modal Header Image with BLK Number and Name -->
                    <div class="relative h-44 w-full overflow-hidden bg-[#0F1420] theme-preserve-dark">
                        <img :src="detailBlock.image_url" :alt="detailBlock.name" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#121927] via-[#121927]/40 to-black/60"></div>
                        
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 rounded-xl font-mono text-xs font-semibold text-emerald-400 bg-[#0B0F17]/90 border border-emerald-500/30 shadow backdrop-blur-md" x-text="detailBlock.block_code"></span>
                        </div>

                        <button type="button" @click="detailModalOpen = false" class="absolute top-4 right-4 p-1.5 rounded-xl bg-black/70 text-slate-400 hover:text-white border border-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <div class="absolute bottom-3 left-4 right-4">
                            <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider block drop-shadow">Dormitory Block Overview</span>
                            <h3 class="text-2xl font-bold text-white tracking-tight drop-shadow-md" x-text="detailBlock.name"></h3>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-white/[0.06]">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-medium uppercase tracking-wider"
                                      :class="detailBlock.gender_category === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'"
                                      x-text="detailBlock.gender_category + ' Students Only'"></span>
                                <span class="text-xs text-slate-400 flex items-center gap-1" x-text="detailBlock.location || 'Campus Residence Zone'"></span>
                            </div>
                        </div>

                        <!-- Statistics Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="p-3.5 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Rooms Configured</span>
                                <span class="text-base font-semibold text-white mt-0.5 block font-mono" x-text="(detailBlock.rooms_count || 0) + ' / 50' "></span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Active Residents</span>
                                <span class="text-base font-semibold text-emerald-400 mt-0.5 block font-mono" x-text="(detailBlock.active_allocations_count || 0) + ' Students'"></span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05] col-span-2 sm:col-span-1">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Design Capacity</span>
                                <span class="text-base font-semibold text-cyan-400 mt-0.5 block font-mono">300 Beds</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-1">
                            <span class="text-[10px] uppercase font-semibold text-slate-400">Description & Notes</span>
                            <p class="text-xs text-slate-300 bg-[#0F1420]/60 p-3.5 rounded-2xl border border-white/[0.05]" x-text="detailBlock.description || 'No additional notes provided for this dormitory block.'"></p>
                        </div>

                        <!-- Modal Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-white/[0.06]">
                            <a :href="'/admin/rooms?block_id=' + detailBlock.id" class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5 active:scale-[0.98]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Manage Rooms in this Block</span>
                            </a>

                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                <button type="button" @click="detailModalOpen = false; openEdit(detailBlock)" class="px-3.5 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                    Edit Block
                                </button>
                                <button type="button" @click="detailModalOpen = false" class="px-3.5 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] border border-white/10 rounded-xl transition-all">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: Edit Dormitory Block                -->
        <!-- ========================================== -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="editModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/[0.08] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 sm:p-7">
                    <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                        <div>
                            <h3 class="text-base font-semibold text-white tracking-tight">Edit Dormitory Block</h3>
                            <p class="text-xs text-slate-400" x-text="'Updating ' + editBlock.block_code"></p>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'/admin/blocks/' + editBlock.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Block Code</label>
                            <input type="text" name="block_code" x-model="editBlock.block_code" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono transition-all" />
                        </div>

                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Block Name</label>
                            <input type="text" name="name" x-model="editBlock.name" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all" />
                        </div>

                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Accommodation Category</label>
                            <select name="gender_category" x-model="editBlock.gender_category" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                <option value="male">Male Students Only</option>
                                <option value="female">Female Students Only</option>
                            </select>
                            <p class="text-[10px] text-amber-400 mt-1">Note: Cannot be changed if block currently has active residents.</p>
                        </div>

                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Campus Location</label>
                            <input type="text" name="location" x-model="editBlock.location" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all" />
                        </div>

                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Description / Notes</label>
                            <textarea name="description" x-model="editBlock.description" rows="2" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all"></textarea>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-white/[0.06]">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98]">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
