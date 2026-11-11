<x-app-layout title="Allocated Students">
    <div class="space-y-6">
        <!-- Top Banner Console -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Resident Placements</span>
                </div>
                <h2 class="text-xl font-bold text-white tracking-tight">Active Resident Allocations</h2>
                <p class="text-xs text-slate-400 mt-0.5">Students currently residing in rooms within your assigned dormitory blocks.</p>
            </div>
            <a href="{{ route('proctor.allocate.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Allocate New Student</span>
            </a>
        </div>

        <!-- Search and Filter Bar -->
        <div class="bg-[#111726]/70 border border-white/[0.06] rounded-2xl p-3">
            <form method="GET" action="{{ route('proctor.allocated.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <div class="relative sm:col-span-2">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student name, ID, or room number..."
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
                        Filter Allocations
                    </button>
                    @if(request()->hasAny(['search', 'block_id', 'gender']))
                        <a href="{{ route('proctor.allocated.index') }}" class="px-3 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.03] hover:bg-white/[0.06] border border-white/10 rounded-xl transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table of Allocations -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>Student</span>
                                    <x-sort-dropdown column="name" label="Student Name" asc-label="Ascending (A → Z)" desc-label="Descending (Z → A)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">Gender</th>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>Block & Room</span>
                                    <x-sort-dropdown column="room_number" label="Room Number" asc-label="Ascending (Room 1 → 50)" desc-label="Descending (Room 50 → 1)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">Academic Year</th>
                            <th class="px-5 py-3.5">Allocated On</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($allocations as $alloc)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $alloc->student->avatar_url }}" alt="" class="w-8 h-8 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <span class="font-semibold text-white block">{{ $alloc->student->name }}</span>
                                            <span class="text-[10px] text-emerald-400 font-mono">{{ $alloc->student->university_id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium capitalize {{ $alloc->student->gender === 'female' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' }}">
                                        {{ $alloc->student->gender ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-white font-semibold">Room {{ $alloc->room->room_number }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $alloc->room->block->name }} ({{ ucfirst($alloc->room->block->gender_category) }})</span>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-slate-300">
                                    Year {{ $alloc->student->academic_year ?? '1' }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-400">
                                    {{ $alloc->allocated_at ? $alloc->allocated_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <form action="{{ route('proctor.allocated.release', $alloc) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to release {{ addslashes($alloc->student->name) }} from Room {{ $alloc->room->room_number }}? This frees up a bed slot.');"
                                          class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl text-[11px] font-medium bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-400 transition-colors">
                                            Release Bed
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    No active resident allocations found matching your search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($allocations->hasPages())
                <div class="p-4 border-t border-white/[0.06]">
                    {{ $allocations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>