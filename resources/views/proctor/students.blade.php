<x-app-layout title="Student Information Directory">
    <div class="space-y-6">
        <!-- Top bar with Title and Scope Summary -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 mb-1 block">Roster Management</span>
                <h2 class="text-2xl font-bold tracking-tight text-white">Department & Batch Student Directory</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">Students in your assigned departments and batches for room placements.</p>
            </div>
            <a href="{{ route('proctor.allocate.index') }}" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Interactive Allocation Engine</span>
            </a>
        </div>

        <!-- Authorized Scope Information Card -->
        <div class="p-5 rounded-2xl bg-[#172134]/60 border border-white/[0.05] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-white">Your Assigned Block & Academic Cohorts</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        Block: <span class="text-white font-semibold">{{ $blocks->pluck('block_code')->join(', ') ?: 'None' }}</span>
                        @if(!empty($blockGenders))
                            &bull; Wing: <span class="capitalize font-semibold text-cyan-300">{{ implode(', ', $blockGenders) }}</span>
                        @endif
                        &bull; Assigned Departments: <span class="text-slate-200 font-medium">{{ count($assignedDepartments) > 0 ? implode(', ', $assignedDepartments) : 'All Departments' }}</span>
                        &bull; Assigned Batches: <span class="text-slate-200 font-medium">{{ count($assignedYears) > 0 ? implode(', ', array_map(fn($y) => "Yr {$y}", $assignedYears)) : 'All Batches' }}</span>
                    </p>
                </div>
            </div>
            <div class="text-xs text-slate-400 shrink-0">
                Total Cohort: <span class="text-white font-bold text-sm font-mono">{{ $students->total() }}</span> students
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="p-4 rounded-2xl bg-[#111726]/70 border border-white/[0.06]">
            <form method="GET" action="{{ route('proctor.students.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="relative sm:col-span-2">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student name, ID, or department..."
                           class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl pl-10 pr-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all" />
                </div>

                <select name="department" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition-all">
                    <option value="">All Assigned Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ strtolower(request('department', '')) === strtolower($dept) ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>

                <select name="year" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition-all">
                    <option value="">All Assigned Batches</option>
                    @foreach(!empty($assignedYears) ? $assignedYears : [1, 2, 3, 4, 5, 6, 7] as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>Year {{ $yr }}</option>
                    @endforeach
                </select>

                <select name="status" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition-all">
                    <option value="">All Allocation Statuses</option>
                    <option value="unallocated" {{ request('status') === 'unallocated' ? 'selected' : '' }}>Unallocated (Needs Room)</option>
                    <option value="allocated" {{ request('status') === 'allocated' ? 'selected' : '' }}>Allocated to Room</option>
                </select>

                <div class="sm:col-span-2 md:col-span-5 flex items-center justify-end gap-2.5 pt-1">
                    @if(request()->hasAny(['search', 'department', 'year', 'status']))
                        <a href="{{ route('proctor.students.index') }}" class="px-4 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                            Reset Filters
                        </a>
                    @endif
                    <button type="submit" class="bg-white hover:bg-slate-100 text-slate-950 font-semibold px-4 py-2 rounded-xl text-xs shadow-md transition-all active:scale-[0.98]">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Table of Students -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">
                                <div class="inline-flex items-center gap-1.5">
                                    <span>Student</span>
                                    <x-sort-dropdown column="name" label="Student Name" asc-label="Ascending (A → Z)" desc-label="Descending (Z → A)" />
                                </div>
                            </th>
                            <th class="px-5 py-4">
                                <div class="inline-flex items-center gap-1.5">
                                    <span>University ID</span>
                                    <x-sort-dropdown column="university_id" label="University ID" asc-label="Ascending (Low → High)" desc-label="Descending (High → Low)" />
                                </div>
                            </th>
                            <th class="px-5 py-4">Gender</th>
                            <th class="px-5 py-4">Department</th>
                            <th class="px-5 py-4">Academic Year</th>
                            <th class="px-5 py-4">Residence Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($students as $st)
                            @php
                                $alloc = $st->activeAllocation;
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $st->avatar_url }}" alt="" class="w-8 h-8 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <span class="font-semibold text-white block">{{ $st->name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $st->email ?? 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono font-medium text-white">
                                    {{ $st->university_id }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium uppercase {{ $st->gender === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-pink-500/10 text-pink-400 border border-pink-500/20' }}">
                                        {{ $st->gender }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-200 font-medium">
                                    {{ $st->department ?? 'General' }}
                                </td>
                                <td class="px-5 py-4 text-slate-300">
                                    {{ $st->academic_year ? $st->academic_year . 'th Year' : 'Undergraduate' }}
                                </td>
                                <td class="px-5 py-4">
                                    @if($alloc && $alloc->room)
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                            <span class="font-medium text-white">
                                                <span class="text-emerald-400 font-mono">{{ $alloc->room->block->block_code }}</span> &bull; Rm {{ $alloc->room->room_number }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 inline-flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            <span>Unallocated</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($alloc)
                                        <form method="POST" action="{{ route('proctor.allocated.release', $alloc) }}" onsubmit="return confirm('Release accommodation for {{ addslashes($st->name) }}?');" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 rounded-lg text-xs font-medium bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/20 transition-colors">
                                                Release
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('proctor.allocate.index', ['student_id' => $st->id]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>Assign Room</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <p class="font-semibold text-white text-sm">No Students Found</p>
                                        <p class="text-xs text-slate-400">No students in your assigned departments and batches match your filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
                <div class="p-4 border-t border-white/[0.06]">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
