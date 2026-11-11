<x-app-layout title="My Assigned Blocks">
    <div class="space-y-6" x-data="{
        detailModalOpen: false,
        detailBlock: {
            id: null,
            name: '',
            block_code: '',
            gender_category: 'male',
            location: '',
            description: '',
            rooms_count: 0,
            active_allocations_count: 0,
            total_capacity: 300,
            available_beds: 300,
            occupancy_rate: 0,
            image_url: ''
        },
        openDetail(b) {
            this.detailBlock = b;
            this.detailModalOpen = true;
        }
    }">
        <div class="relative z-20 bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Field Operations</span>
                </div>
                <h2 class="text-xl font-bold text-white tracking-tight">Your Authorized Dormitory Blocks</h2>
                <p class="text-xs text-slate-400 mt-0.5">You are authorized to manage rooms and allocate students within these designated halls.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('proctor.rooms.index') }}" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>View All Rooms</span>
                </a>
                <a href="{{ route('proctor.allocate.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>Allocate Students</span>
                </a>
                <x-block-sort-dropdown />
            </div>
        </div>

        <div class="{{ $blocks->count() <= 1 ? 'max-w-5xl' : 'grid grid-cols-1 lg:grid-cols-2 gap-6' }}">
            @forelse($blocks as $b)
                <div class="rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] hover:border-white/15 transition-all overflow-hidden flex flex-col justify-between shadow-xl shadow-black/25 group">
                    <!-- Image Area: Fully covering the block card with Name and Number overlaid directly on it -->
                    <div class="relative w-full {{ $blocks->count() <= 1 ? 'h-64 sm:h-72' : 'h-56 sm:h-64' }} overflow-hidden bg-[#0D1117] theme-preserve-dark">
                        <img src="{{ $b->image_url }}" alt="{{ $b->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        
                        <!-- Rich Dark Gradient Overlay for optimal readability -->
                        <div class="absolute inset-0 bg-gradient-to-t from-[#121927] via-[#121927]/40 to-black/60 pointer-events-none"></div>

                        <!-- Top row ON Image: Block Number Badge and View Detail quick button -->
                        <div class="absolute top-4 left-4 right-4 flex items-center justify-between z-10">
                            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl font-mono text-xs sm:text-sm font-semibold text-emerald-400 bg-slate-950/80 border border-emerald-500/30 shadow-lg backdrop-blur-md">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $b->block_code }}
                            </span>

                            <button type="button" @click="openDetail({{ Js::from($b) }})" title="View Block Details" class="px-3 py-1.5 text-xs font-medium text-white bg-slate-950/70 hover:bg-slate-900 border border-white/20 hover:border-white/40 rounded-xl backdrop-blur-md transition-all shadow flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Details
                            </button>
                        </div>

                        <!-- Bottom row ON Image: Block Name and Subtitle directly on the image -->
                        <div class="absolute bottom-4 left-4 right-4 z-10">
                            <span class="text-[10px] sm:text-xs font-semibold text-emerald-400 uppercase tracking-widest block mb-0.5 drop-shadow">Dormitory Block</span>
                            <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight drop-shadow-md truncate">{{ $b->name }}</h3>
                        </div>
                    </div>

                    <!-- Details Area: Written BELOW the image -->
                    <div class="p-5 sm:p-6 space-y-4">
                        <!-- Heading row below image: BLK Code, Name, Gender Category & Location -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-white/[0.06]">
                            <div>
                                <span class="text-xs font-mono font-semibold text-emerald-400 uppercase tracking-wider">{{ $b->block_code }}</span>
                                <h4 class="text-lg font-bold text-white tracking-tight">{{ $b->name }}</h4>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-[11px] font-medium uppercase tracking-wider {{ $b->gender_category === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                    {{ $b->gender_category }} Block
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center text-xs text-slate-400 gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $b->location ?? 'Campus Residence Zone' }}</span>
                        </div>

                        <!-- Statistics Grid below Image -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                            <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Configured</span>
                                <span class="text-base font-bold text-white mt-0.5 block">{{ $b->rooms_count }} / 50 Rooms</span>
                            </div>
                            <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Occupancy</span>
                                <span class="text-base font-bold text-emerald-400 mt-0.5 block">{{ $b->active_allocations_count }} / {{ $b->total_capacity }}</span>
                            </div>
                            <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Free Beds</span>
                                <span class="text-base font-bold text-white mt-0.5 block">{{ $b->available_beds }} Beds</span>
                            </div>
                            <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Rate</span>
                                <span class="text-base font-bold text-cyan-400 mt-0.5 block">{{ $b->occupancy_rate }}%</span>
                            </div>
                        </div>

                        @if($b->description)
                            <p class="text-xs text-slate-400 line-clamp-2">{{ $b->description }}</p>
                        @endif

                        <!-- Dedicated Action Row -->
                        <div class="pt-3 border-t border-white/[0.06] flex items-center justify-between gap-3">
                            <button type="button" @click="openDetail({{ Js::from($b) }})" class="px-3.5 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>View Details</span>
                            </button>

                            <a href="{{ route('proctor.rooms.index', ['block_id' => $b->id]) }}" class="px-4 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Manage Rooms &rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] text-center col-span-2">
                    <p class="text-xs text-slate-400">You are not currently assigned to any dormitory blocks. Please contact the Proctor Manager.</p>
                </div>
            @endforelse
        </div>

        <!-- ========================================== -->
        <!-- MODAL: View Block Details (Proctor)       -->
        <!-- ========================================== -->
        <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="detailModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/10 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- Modal Header Image -->
                    <div class="relative h-44 w-full overflow-hidden bg-[#0D1117] theme-preserve-dark">
                        <img :src="detailBlock.image_url" :alt="detailBlock.name" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#121927] via-[#121927]/40 to-black/60"></div>
                        
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 rounded-xl font-mono text-xs font-semibold text-emerald-400 bg-black/80 border border-emerald-500/30 shadow backdrop-blur-md" x-text="detailBlock.block_code"></span>
                        </div>

                        <button type="button" @click="detailModalOpen = false" class="absolute top-4 right-4 p-1.5 rounded-xl bg-black/70 text-slate-400 hover:text-white border border-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <div class="absolute bottom-3 left-4 right-4">
                            <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider block">Dormitory Block Overview</span>
                            <h3 class="text-2xl font-bold text-white tracking-tight" x-text="detailBlock.name"></h3>
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
                            <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Rooms Configured</span>
                                <span class="text-base font-bold text-white mt-0.5 block" x-text="(detailBlock.rooms_count || 0) + ' / 50' "></span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Active Residents</span>
                                <span class="text-base font-bold text-emerald-400 mt-0.5 block" x-text="(detailBlock.active_allocations_count || 0) + ' Students'"></span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06] col-span-2 sm:col-span-1">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Free Capacity</span>
                                <span class="text-base font-bold text-cyan-400 mt-0.5 block" x-text="(detailBlock.available_beds || 0) + ' Free Beds'"></span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-1.5">
                            <span class="text-[10px] uppercase font-semibold text-slate-400">Description & Details</span>
                            <p class="text-xs text-slate-300 bg-[#0F1420] p-3.5 rounded-2xl border border-white/[0.06]" x-text="detailBlock.description || 'No additional notes provided for this dormitory block.'"></p>
                        </div>

                        <!-- Modal Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-white/[0.06]">
                            <a :href="'/proctor/rooms?block_id=' + detailBlock.id" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Manage Rooms in this Block</span>
                            </a>

                            <button type="button" @click="detailModalOpen = false" class="w-full sm:w-auto px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>