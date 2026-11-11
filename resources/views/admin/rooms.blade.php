<x-app-layout title="Room Inventory">
    <div class="space-y-6" x-data="{
        residentModalOpen: false,
        selectedRoom: {
            room_number: '',
            block_code: '',
            block_name: '',
            gender_category: '',
            capacity: 6,
            active_allocations: []
        },
        openResidents(r) {
            this.selectedRoom = {
                room_number: r.room_number,
                block_code: r.block ? r.block.block_code : '',
                block_name: r.block ? r.block.name : '',
                gender_category: r.block ? r.block.gender_category : '',
                capacity: r.capacity,
                active_allocations: r.active_allocations || []
            };
            this.residentModalOpen = true;
        }
    }">
        <!-- Register Room Form -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90 mb-1 block">Facility Management</span>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Register Room in Block</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 mb-5 max-w-2xl leading-relaxed">Add a dormitory room with fixed student capacity (max 6 beds per room).</p>

            <form method="POST" action="{{ route('admin.rooms.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Dormitory Block</label>
                    <select name="block_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                        @foreach($blocks as $b)
                            <option value="{{ $b->id }}">{{ $b->block_code }} - {{ $b->name }} ({{ ucfirst($b->gender_category) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Room Number</label>
                    <input type="text" name="room_number" required placeholder="e.g. 105" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono placeholder-slate-500 transition-all" />
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Capacity (Max 6 Beds)</label>
                    <input type="number" name="capacity" value="6" min="1" max="6" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2.5 px-4 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98] flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>+ Register Room</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Search and Filter Bar -->
        <div class="p-4 rounded-2xl bg-[#111726]/70 border border-white/[0.06] backdrop-blur-md">
            <form method="GET" action="{{ route('admin.rooms.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="relative sm:col-span-2">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search block (e.g. BLK-45) or room (e.g. 101)..."
                           class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl pl-9 pr-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                </div>

                <select name="block_id" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Blocks</option>
                    @foreach($blocks as $b)
                        <option value="{{ $b->id }}" {{ request('block_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->block_code }} - {{ $b->name }}
                        </option>
                    @endforeach
                </select>

                <select name="gender" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Genders</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male Blocks</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female Blocks</option>
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-md shadow-emerald-500/20 active:scale-[0.98]">
                        Search Rooms
                    </button>
                    @if(request()->hasAny(['search', 'block_id', 'gender']))
                        <a href="{{ route('admin.rooms.index') }}" class="px-3.5 py-2.5 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] border border-white/10 rounded-xl transition-all flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Rooms Information: Clean List View Table with Pagination -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl">
            <div class="p-5 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight flex items-center gap-2">
                        <span>Room Inventory (List View)</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-white/[0.05] text-slate-300 border border-white/10 font-mono">
                            Total: {{ $rooms->total() }} rooms
                        </span>
                    </h3>
                    <p class="text-[11px] text-slate-400">Complete list of dormitory rooms, block associations, and bed occupancy.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.03] text-slate-400 uppercase text-[10px] font-semibold border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>Room</span>
                                    <x-sort-dropdown column="room_number" label="Room Number" asc-label="Ascending (Room 1 → 50)" desc-label="Descending (Room 50 → 1)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>Dormitory Block</span>
                                    <x-sort-dropdown column="block" label="Dormitory Block" asc-label="Ascending (BLK-01 → BLK-50)" desc-label="Descending (BLK-50 → BLK-01)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Capacity</th>
                            <th class="px-5 py-3.5">Occupancy</th>
                            <th class="px-5 py-3.5">Free Beds</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Residents</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($rooms as $rm)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $rm->image_url }}" alt="Room {{ $rm->room_number }}" class="w-12 h-10 rounded-lg object-cover border border-white/10 shrink-0" />
                                        <div>
                                            <span class="font-semibold text-white text-sm block">Room {{ $rm->room_number }}</span>
                                            <span class="text-[10px] font-mono text-emerald-400">{{ $rm->block->block_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-medium text-white block">{{ $rm->block->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $rm->block->location ?? 'Campus Residence Zone' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider {{ $rm->block->gender_category === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $rm->block->gender_category }} Block
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-slate-200">
                                    <span>{{ $rm->capacity }} Beds</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold font-mono text-xs {{ $rm->active_allocations_count >= $rm->capacity ? 'text-rose-400' : 'text-emerald-400' }}">
                                                {{ $rm->active_allocations_count }} / {{ $rm->capacity }}
                                            </span>
                                        </div>
                                        <div class="w-24 bg-white/[0.06] h-1.5 rounded-full overflow-hidden">
                                            @php
                                                $pct = $rm->capacity > 0 ? min(100, round(($rm->active_allocations_count / $rm->capacity) * 100)) : 0;
                                            @endphp
                                            <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-rose-500' : 'bg-emerald-400' }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $free = max(0, $rm->capacity - $rm->active_allocations_count);
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium {{ $free > 0 ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/20' : 'text-rose-400 bg-rose-500/10 border border-rose-500/20' }}">
                                        {{ $free > 0 ? "{$free} Free" : 'Full' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium capitalize {{ $rm->status === 'available' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $rm->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <button type="button" @click="openResidents({{ Js::from($rm) }})" class="px-3.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all shadow-sm flex items-center gap-1.5 ml-auto">
                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <span>Residents ({{ $rm->active_allocations_count }})</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-12 text-center text-slate-400 text-xs">
                                    No rooms found matching your search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- List View Pagination -->
            <div class="p-4 border-t border-white/[0.06]">
                {{ $rooms->links() }}
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- MODAL: View Current Residents in Room (Administrator) -->
        <!-- ==================================================== -->
        <div x-show="residentModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="residentModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/[0.08] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 sm:p-7">
                    <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                        <div>
                            <span class="text-[10px] font-mono text-emerald-400 uppercase font-semibold" x-text="selectedRoom.block_code + ' &bull; ' + selectedRoom.block_name"></span>
                            <h3 class="text-base font-semibold text-white tracking-tight" x-text="'Room ' + selectedRoom.room_number + ' Resident Students'"></h3>
                        </div>
                        <button type="button" @click="residentModalOpen = false" class="p-1 text-slate-400 hover:text-white rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4 mt-4">
                        <div class="flex items-center justify-between text-xs p-3.5 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Category</span>
                                <span class="font-medium text-white uppercase" x-text="selectedRoom.gender_category + ' BLOCK'"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Beds</span>
                                <span class="font-semibold text-emerald-400 font-mono" x-text="selectedRoom.active_allocations.length + ' / ' + selectedRoom.capacity + ' Occupied'"></span>
                            </div>
                        </div>

                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            <template x-if="selectedRoom.active_allocations.length === 0">
                                <div class="p-6 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05] text-center text-xs text-slate-400">
                                    No students are currently resident in this room.
                                </div>
                            </template>

                            <template x-for="alloc in selectedRoom.active_allocations" :key="alloc.id">
                                <div class="p-3.5 rounded-xl bg-[#0F1420]/60 border border-white/[0.05] flex items-center justify-between gap-3">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-semibold text-white" x-text="alloc.student ? alloc.student.name : 'Unknown'"></span>
                                            <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full" x-text="alloc.student ? alloc.student.university_id : ''"></span>
                                        </div>
                                        <p class="text-[10px] text-slate-400" x-text="(alloc.student ? alloc.student.department : '') + (alloc.student && alloc.student.academic_year ? ' &bull; Year ' + alloc.student.academic_year : '')"></p>
                                    </div>
                                    <span class="text-[10px] text-slate-400 capitalize" x-text="alloc.status"></span>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-white/[0.06]">
                            <button type="button" @click="residentModalOpen = false" class="px-4 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
