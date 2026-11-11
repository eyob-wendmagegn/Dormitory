<x-app-layout title="Student Information Directory">
    <div class="space-y-6">
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90 mb-1 block">Roster Directory</span>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Student Residents Directory</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">Filter undergraduate students by gender, academic year, and residence placement.</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="p-4 rounded-2xl bg-[#111726]/70 border border-white/[0.06] backdrop-blur-md">
            <form method="GET" action="{{ route('admin.students.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, ID or department..." class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />

                <select name="department" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>

                <select name="gender" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Genders</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male Students</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female Students</option>
                </select>

                <select name="year" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Academic Years</option>
                    @foreach([1, 2, 3, 4, 5, 6, 7] as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}th Year</option>
                    @endforeach
                </select>

                <select name="status" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Allocation Statuses</option>
                    <option value="allocated" {{ request('status') === 'allocated' ? 'selected' : '' }}>Allocated to Room</option>
                    <option value="unallocated" {{ request('status') === 'unallocated' ? 'selected' : '' }}>Unallocated</option>
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-md shadow-emerald-500/20 active:scale-[0.98]">
                        Apply Filters
                    </button>
                    @if(request()->hasAny(['search', 'department', 'gender', 'year', 'status']))
                        <a href="{{ route('admin.students.index') }}" class="px-3.5 py-2.5 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] border border-white/10 rounded-xl transition-all flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Students Table -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.03] text-slate-400 uppercase text-[10px] font-semibold border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>Student</span>
                                    <x-sort-dropdown column="name" label="Student Name" asc-label="Ascending (A → Z)" desc-label="Descending (Z → A)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>ID Number</span>
                                    <x-sort-dropdown column="university_id" label="University ID" asc-label="Ascending (Low → High)" desc-label="Descending (High → Low)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">Gender</th>
                            <th class="px-5 py-3.5">Department</th>
                            <th class="px-5 py-3.5">Academic Year</th>
                            <th class="px-5 py-3.5">Assigned Residence</th>
                            <th class="px-5 py-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($students as $st)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $st->avatar_url }}" alt="{{ $st->name }}" class="w-7 h-7 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <span class="font-medium text-white block">{{ $st->name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $st->email ?? 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-emerald-400 font-mono">{{ $st->university_id }}</td>
                                <td class="px-5 py-3.5 capitalize">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium {{ $st->gender === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $st->gender ?? 'Not Set' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-200">{{ $st->department ?? 'General' }}</td>
                                <td class="px-5 py-3.5 text-slate-300">{{ $st->academic_year_label ?? 'Undergraduate' }}</td>
                                <td class="px-5 py-3.5">
                                    @if($st->activeAllocation)
                                        <span class="font-medium text-white block">
                                            {{ $st->activeAllocation->room->block->name }} ({{ $st->activeAllocation->room->block->block_code }})
                                        </span>
                                        <span class="text-emerald-400 font-mono text-[11px]">Room {{ $st->activeAllocation->room->room_number }}</span>
                                    @else
                                        <span class="text-slate-500">Not Allocated</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($st->activeAllocation)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Accommodated
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Awaiting Room
                                        </span>
                                    @endif
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
