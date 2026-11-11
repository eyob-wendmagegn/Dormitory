<x-app-layout title="Student Placement Overview">
    <div class="space-y-6">
        <!-- Header Console Banner -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25">
            <div class="flex items-center gap-2 mb-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Campus Demographics</span>
            </div>
            <h2 class="text-xl font-bold text-white tracking-tight">Student Residents Placement Roster</h2>
            <p class="text-xs text-slate-400 mt-0.5">Supervisory view of campus dormitory student allocations with gender and placement indicators.</p>
        </div>

        <!-- Filters Form Toolbar -->
        <div class="bg-[#111726]/70 border border-white/[0.06] rounded-2xl p-3">
            <form method="GET" action="{{ route('manager.students.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
                <div class="relative sm:col-span-2">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, ID, department or block..."
                           class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white placeholder-slate-500 transition-all" />
                </div>

                <select name="department" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white transition-all">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>

                <select name="gender" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white transition-all">
                    <option value="">All Genders</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                </select>

                <select name="status" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white transition-all">
                    <option value="">All Placements</option>
                    <option value="allocated" {{ request('status') === 'allocated' ? 'selected' : '' }}>Housed (Allocated)</option>
                    <option value="unallocated" {{ request('status') === 'unallocated' ? 'selected' : '' }}>Pending (Unallocated)</option>
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                        Filter Students
                    </button>
                    @if(request()->hasAny(['search', 'department', 'gender', 'status', 'year']))
                        <a href="{{ route('manager.students.index') }}" class="px-3 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.03] hover:bg-white/[0.06] border border-white/10 rounded-xl transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card -->
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
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>University ID</span>
                                    <x-sort-dropdown column="university_id" label="University ID" asc-label="Ascending (Low → High)" desc-label="Descending (High → Low)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">Gender</th>
                            <th class="px-5 py-3.5">Department</th>
                            <th class="px-5 py-3.5">Academic Year</th>
                            <th class="px-5 py-3.5">Current Placement</th>
                            <th class="px-5 py-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($students as $st)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $st->avatar_url }}" alt="" class="w-7 h-7 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <span class="font-semibold text-white block">{{ $st->name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $st->email ?? 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-emerald-400 font-mono">{{ $st->university_id }}</td>
                                <td class="px-5 py-3.5 capitalize">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium {{ $st->gender === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $st->gender ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-300">{{ $st->department ?? 'General' }}</td>
                                <td class="px-5 py-3.5 text-slate-300">
                                    {{ $st->academic_year_label ?? 'Year ' . ($st->academic_year ?? 1) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($st->activeAllocation)
                                        <div class="text-white font-medium flex items-center gap-1.5">
                                            <span class="text-emerald-400 font-semibold font-mono">{{ $st->activeAllocation->room->block->block_code }}</span>
                                            <span>&bull; Rm {{ $st->activeAllocation->room->room_number }}</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block">{{ $st->activeAllocation->room->block->name }}</span>
                                    @else
                                        <span class="text-slate-500 italic">Unallocated</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium {{ $st->activeAllocation ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                                        {{ $st->activeAllocation ? 'Housed' : 'Pending' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    No students found matching your search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-white/[0.06]">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</x-app-layout>