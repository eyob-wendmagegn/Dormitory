<x-app-layout title="Manage Rooms">
    <div class="space-y-6" x-data="{
        assignmentModalOpen: false,
        activeRoom: {
            id: null,
            room_number: '',
            capacity: 6,
            image_url: '',
            block: null,
            active_allocations: []
        },
        get activeAllocations() {
            return this.activeRoom.active_allocations || [];
        },
        openAssignmentModal(room) {
            this.activeRoom = room;
            this.assignmentModalOpen = true;
        }
    }">
        <!-- Register Room Form Console -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25">
            <div class="flex items-center gap-2 mb-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Wing Operations</span>
            </div>
            <h2 class="text-xl font-bold text-white tracking-tight mb-1">Register Room in Your Block</h2>
            <p class="text-xs text-slate-400 mb-5">Add a room to one of your authorized dormitory blocks. The room image is automatically assigned based on the room number logic.</p>

            <form method="POST" action="{{ route('proctor.rooms.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Authorized Block</label>
                    <select name="block_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                        @foreach($blocks as $b)
                            <option value="{{ $b->id }}">{{ $b->block_code }} - {{ $b->name }} ({{ $b->gender_category }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Room Number</label>
                    <input type="text" name="room_number" required placeholder="e.g. 106" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono placeholder-slate-500" />
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Capacity (Max 6 Beds)</label>
                    <input type="number" name="capacity" value="6" min="1" max="6" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2.5 px-4 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Save Room</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Search and Filter Bar -->
        <div class="relative z-20 bg-[#111726]/70 border border-white/[0.06] rounded-2xl p-3">
            <form method="GET" action="{{ route('proctor.rooms.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <div class="relative sm:col-span-2">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search room number (e.g. 101) or block..."
                           class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white placeholder-slate-500 transition-all" />
                </div>

                <select name="block_id" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white transition-all">
                    <option value="">All Authorized Blocks</option>
                    @foreach($blocks as $b)
                        <option value="{{ $b->id }}" {{ request('block_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->block_code }} - {{ $b->name }}
                        </option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                        Search Rooms
                    </button>
                    <x-room-filter-sort-dropdown />
                    @if(request()->hasAny(['search', 'block_id', 'status', 'sort']))
                        <a href="{{ route('proctor.rooms.index') }}" class="px-3 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.03] hover:bg-white/[0.06] border border-white/10 rounded-xl transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Rooms Grid: 5 cards per row with minimized card height -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5">
            @forelse($rooms as $rm)
                <div class="rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] hover:border-white/15 transition-all overflow-hidden flex flex-col justify-between group shadow-lg shadow-black/20">
                    <!-- Top Section: Minimized Room Image (h-26 sm:h-28) -->
                    <div class="relative h-28 w-full overflow-hidden bg-[#0D1117] theme-preserve-dark">
                        <img src="{{ $rm->image_url }}" alt="Room {{ $rm->room_number }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        
                        <!-- Scrim Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-[#121927] via-[#121927]/30 to-black/50 pointer-events-none"></div>

                        <!-- Top Overlay: Block Code Badge on Image -->
                        <div class="absolute top-2 left-2 z-10">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold text-emerald-400 bg-slate-950/80 backdrop-blur-sm border border-emerald-500/30 shadow">
                                {{ $rm->block->block_code }}
                            </span>
                        </div>

                        <!-- Top Right: Gender Badge & Edit Assignment Button directly on Image -->
                        <div class="absolute top-2 right-2 flex items-center gap-1 z-10">
                            <span class="text-[9px] uppercase font-medium px-2 py-0.5 rounded-full {{ $rm->block->gender_category === 'male' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }} backdrop-blur-sm">
                                {{ $rm->block->gender_category }}
                            </span>

                            <!-- Edit Student Assignment icon button on the card image -->
                            <button type="button" @click="openAssignmentModal({{ Js::from($rm) }})" title="Edit Student Assignment" class="p-1 rounded-lg bg-slate-950/85 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/30 shadow backdrop-blur-md transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                        </div>

                        <!-- Bottom Overlay: Room Number written directly on the image -->
                        <div class="absolute bottom-2 left-2.5 right-2.5 z-10">
                            <h4 class="text-base font-bold text-white drop-shadow-md tracking-tight leading-none">Room {{ $rm->room_number }}</h4>
                        </div>
                    </div>

                    <!-- Bottom Section: Other Info Written BELOW the image -->
                    <div class="p-3.5 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400 text-[10px]">Beds: <strong class="text-white">{{ $rm->capacity }}</strong></span>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-medium capitalize {{ $rm->status === 'available' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                {{ $rm->status }}
                            </span>
                        </div>

                        <!-- Mini Occupancy Bar -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="text-slate-400">Occupancy</span>
                                <span class="font-semibold {{ $rm->occupied_beds >= $rm->capacity ? 'text-rose-400' : 'text-emerald-400' }}">
                                    {{ $rm->occupied_beds }} / {{ $rm->capacity }}
                                </span>
                            </div>
                            <div class="w-full bg-white/[0.06] h-1.5 rounded-full overflow-hidden">
                                @php
                                    $pct = $rm->capacity > 0 ? min(100, round(($rm->occupied_beds / $rm->capacity) * 100)) : 0;
                                @endphp
                                <div class="h-full {{ $pct >= 100 ? 'bg-rose-500' : 'bg-emerald-400' }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        <!-- Card Footer with Block Name & Edit Assignment Button -->
                        <div class="pt-2 border-t border-white/[0.06] flex items-center justify-between gap-1.5">
                            <span class="text-[10px] text-slate-400 truncate">{{ $rm->block->name }}</span>
                            <button type="button" @click="openAssignmentModal({{ Js::from($rm) }})" class="px-2 py-1 text-[10px] font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-lg transition-all shadow-sm flex items-center gap-1 shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Assign</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4 xl:col-span-5 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] text-center text-xs text-slate-400">
                    No rooms registered in your assigned blocks yet.
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        <div class="pt-2">
            {{ $rooms->links() }}
        </div>

        <!-- ==================================================== -->
        <!-- MODAL: Manage / Edit Student Assignment for Room     -->
        <!-- ==================================================== -->
        <div x-show="assignmentModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="assignmentModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/10 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- Modal Header Image -->
                    <div class="relative h-32 w-full overflow-hidden bg-[#0D1117] theme-preserve-dark">
                        <img :src="activeRoom.image_url" :alt="'Room ' + activeRoom.room_number" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#121927] via-[#121927]/50 to-black/60 pointer-events-none"></div>
                        
                        <div class="absolute top-3 left-3 z-10">
                            <span class="px-2.5 py-1 rounded-lg font-mono text-xs font-semibold text-emerald-400 bg-black/80 border border-emerald-500/30 backdrop-blur-sm" x-text="activeRoom.block ? activeRoom.block.block_code : ''"></span>
                        </div>

                        <button type="button" @click="assignmentModalOpen = false" class="absolute top-3 right-3 p-1.5 rounded-xl bg-black/70 text-slate-400 hover:text-white border border-white/10 transition-colors z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <div class="absolute bottom-2.5 left-3 right-3 z-10">
                            <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider block drop-shadow">Room Assignment Manager</span>
                            <h3 class="text-xl font-bold text-white drop-shadow-md tracking-tight truncate" x-text="'Room ' + activeRoom.room_number + (activeRoom.block ? ' &bull; ' + activeRoom.block.name : '')"></h3>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-5 space-y-4">
                        <!-- Room Status & Capacity Bar -->
                        <div class="flex items-center justify-between text-xs p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Accommodation</span>
                                <span class="text-white font-semibold" x-text="(activeRoom.block ? activeRoom.block.gender_category : '').toUpperCase() + ' STUDENTS'"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Beds Occupied</span>
                                <span class="font-bold" :class="activeAllocations.length >= activeRoom.capacity ? 'text-rose-400' : 'text-emerald-400'" x-text="activeAllocations.length + ' / ' + activeRoom.capacity + ' Beds'"></span>
                            </div>
                        </div>

                        <!-- Current Assigned Students -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Current Resident Students</h4>
                                <span class="text-[11px] text-slate-400" x-text="activeAllocations.length + ' student(s) assigned'"></span>
                            </div>

                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                <template x-if="activeAllocations.length === 0">
                                    <div class="p-6 rounded-2xl bg-[#0F1420] border border-white/[0.06] text-center text-xs text-slate-500">
                                        No students are currently allocated to this room.
                                    </div>
                                </template>

                                <template x-for="alloc in activeAllocations" :key="alloc.id">
                                    <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06] flex items-center justify-between gap-3">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-semibold text-white" x-text="alloc.student ? alloc.student.name : 'Unknown'"></span>
                                                <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md" x-text="alloc.student ? alloc.student.university_id : ''"></span>
                                            </div>
                                            <p class="text-[11px] text-slate-400" x-text="(alloc.student ? alloc.student.department : '') + (alloc.student && alloc.student.academic_year ? ' &bull; Year ' + alloc.student.academic_year : '')"></p>
                                        </div>

                                        <!-- Release Action Form -->
                                        <form :action="'/proctor/allocations/' + alloc.id + '/release'" method="POST" onsubmit="return confirm('Release accommodation for this student?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-[11px] font-medium text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 rounded-lg transition-colors">
                                                Release
                                            </button>
                                        </form>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Policy notice -->
                        <div class="p-3 rounded-xl bg-[#0F1420] border border-white/[0.06] text-[11px] text-slate-400 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Proctors can allocate and release students. Rooms cannot be deleted by proctors.</span>
                        </div>

                        <!-- Modal Action Footer -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-white/[0.06]">
                            <template x-if="activeAllocations.length < activeRoom.capacity">
                                <a :href="'/proctor/allocate?room_id=' + activeRoom.id" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                    <span>+ Allocate Student to Room</span>
                                </a>
                            </template>
                            <template x-if="activeAllocations.length >= activeRoom.capacity">
                                <span class="text-xs font-semibold text-rose-400 flex items-center gap-1">
                                    Room is at full capacity (6/6)
                                </span>
                            </template>

                            <button type="button" @click="assignmentModalOpen = false" class="w-full sm:w-auto px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>