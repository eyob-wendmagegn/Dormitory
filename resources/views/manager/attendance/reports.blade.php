<x-app-layout title="Campus Attendance Analytics & Reports">
    <div class="space-y-6">
        <!-- Top Banner Console -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Campus-Wide Curfew Compliance Analytics</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Dormitory Attendance Reports</h2>
                <p class="text-xs text-slate-400 mt-0.5 max-w-xl">
                    Comprehensive reporting on nightly curfew inspections, proctor round submissions, and chronic absenteeism across all campus halls.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button onclick="window.print()" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Report</span>
                </button>
                <a href="{{ route('attendance.export_csv', ['date' => $dateTo, 'type' => 'all']) }}" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- Filter Range Form Toolbar -->
        <div class="bg-[#111726]/70 border border-white/[0.06] rounded-2xl p-4">
            <form method="GET" action="{{ route(auth()->user()->isAdmin() ? 'admin.attendance.reports' : 'manager.attendance.reports') }}" class="flex flex-wrap items-center gap-3">
                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 text-xs font-medium text-white rounded-xl px-3.5 py-2">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 text-xs font-medium text-white rounded-xl px-3.5 py-2">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1">Filter Block</label>
                    <select name="block_id" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 text-xs font-medium text-white rounded-xl px-3.5 py-2">
                        <option value="">All Dormitory Blocks</option>
                        @foreach($blocks as $b)
                            <option value="{{ $b->id }}" {{ $blockId == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->block_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end self-end">
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 transition-all active:scale-[0.98]">
                        Filter Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Metric KPI Cards for Selected Period -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-slate-400 block tracking-wider">Compliance Rate</span>
                <span class="text-2xl font-bold text-emerald-400 mt-1 block font-mono">{{ $overallRate }}%</span>
                <span class="text-[10px] text-slate-500">Period Average</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-slate-300 block tracking-wider">Inspections</span>
                <span class="text-2xl font-bold text-white mt-1 block font-mono">{{ $totalRecorded }}</span>
                <span class="text-[10px] text-slate-500">Resident Checks</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-emerald-400 block tracking-wider">Present</span>
                <span class="text-2xl font-bold text-emerald-400 mt-1 block font-mono">{{ $totalPresent }}</span>
                <span class="text-[10px] text-slate-500">Room Confirmed</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-rose-400 block tracking-wider">Absences</span>
                <span class="text-2xl font-bold text-rose-400 mt-1 block font-mono">{{ $totalAbsent }}</span>
                <span class="text-[10px] text-rose-400/80">Unexcused Total</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-amber-400 block tracking-wider">Authorized Leave</span>
                <span class="text-2xl font-bold text-amber-400 mt-1 block font-mono">{{ $totalExcused }}</span>
                <span class="text-[10px] text-amber-400/80">Gate Pass Total</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-cyan-400 block tracking-wider">Sick / Clinic</span>
                <span class="text-2xl font-bold text-cyan-400 mt-1 block font-mono">{{ $totalSick }}</span>
                <span class="text-[10px] text-slate-500">Medical Cases</span>
            </div>
        </div>

        <!-- Chronic Absenteeism Watchlist -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-4 sm:p-5 border-b border-white/[0.06] flex items-center justify-between bg-rose-500/5">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                    <h3 class="text-xs font-semibold text-white uppercase tracking-wider">
                        Chronic Absenteeism Watchlist (Repeat Violations)
                    </h3>
                </div>
                <span class="text-[11px] text-slate-400">Threshold: &ge; 2 Absences in Period</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 uppercase text-[10px] font-semibold text-slate-400 tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Student</th>
                            <th class="px-5 py-3.5">Hall & Room</th>
                            <th class="px-5 py-3.5">Department & Year</th>
                            <th class="px-5 py-3.5">Absences in Period</th>
                            <th class="px-5 py-3.5">Risk Level</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($chronicAbsentees as $item)
                            @php
                                $st = $item->student;
                                $room = $st?->activeAllocation?->room;
                                $block = $room?->block;
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $st?->avatar_url }}" alt="{{ $st?->name }}" class="w-8 h-8 rounded-lg object-cover border border-white/10">
                                        <div>
                                            <span class="block font-semibold text-white">{{ $st?->name }}</span>
                                            <span class="block text-[10px] text-slate-400 font-mono">{{ $st?->university_id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-white">
                                    {{ $block?->name }} ({{ $block?->block_code }}) - Rm {{ $room?->room_number ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-white">{{ $st?->department ?? 'General' }}</span>
                                    <span class="block text-[10px] text-slate-400">{{ $st?->academic_year_label ?? 'Undergrad' }}</span>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-rose-400 font-mono text-sm">
                                    {{ $item->absence_count }} Times
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($item->absence_count >= 3)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            High Risk (Disciplinary)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Moderate Risk
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No chronic absenteeism repeat violations recorded in this time range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submitted Weekly & Monthly Reports from Proctors -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-4 sm:p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <h3 class="text-xs font-semibold text-white uppercase tracking-wider">
                        Submitted Weekly &amp; Monthly Reports from Proctors
                    </h3>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">Formal Periodic Submissions</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 uppercase text-[10px] font-semibold text-slate-400 tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Report ID</th>
                            <th class="px-5 py-3.5">Type</th>
                            <th class="px-5 py-3.5">Block</th>
                            <th class="px-5 py-3.5">Reporting Proctor</th>
                            <th class="px-5 py-3.5">Period</th>
                            <th class="px-5 py-3.5 text-center">Tracked Days</th>
                            <th class="px-5 py-3.5 text-center">Total Absences</th>
                            <th class="px-5 py-3.5 text-center">Frequent Absentees</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Submitted</th>
                            <th class="px-5 py-3.5 text-right">Review Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($submittedPeriodicReports ?? [] as $rep)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 font-mono font-semibold text-white">#REP-{{ $rep->id }}</td>
                                <td class="px-5 py-3.5 font-semibold uppercase text-emerald-400">{{ $rep->report_type }}</td>
                                <td class="px-5 py-3.5 text-white font-medium">{{ $rep->block->name }} ({{ $rep->block->block_code }})</td>
                                <td class="px-5 py-3.5 text-slate-300">{{ $rep->proctor->name }}</td>
                                <td class="px-5 py-3.5 font-mono text-slate-300 text-[11px]">
                                    {{ $rep->start_date->format('M d') }} &rarr; {{ $rep->end_date->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-center text-white">{{ $rep->tracked_days_count }}</td>
                                <td class="px-5 py-3.5 text-center font-semibold text-rose-400">{{ $rep->total_absent }}</td>
                                <td class="px-5 py-3.5 text-center font-semibold text-amber-400">{{ $rep->frequent_absentees_count }}</td>
                                <td class="px-5 py-3.5">
                                    @if($rep->status === 'reviewed')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Reviewed
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Submitted
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-400 font-mono">
                                    {{ $rep->submitted_at->format('H:i, M d') }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <form method="POST" action="{{ route(auth()->user()->isAdmin() ? 'admin.attendance.reports.review' : 'manager.attendance.reports.review', $rep) }}" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        <input type="text" name="manager_remarks" placeholder="Add remark..." value="{{ $rep->manager_remarks }}" class="bg-[#0F1420] border border-white/[0.08] text-[11px] rounded-lg px-2.5 py-1 text-white placeholder-slate-500 w-28 focus:border-emerald-500">
                                        <button type="submit" class="px-3 py-1 text-[11px] font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-lg shadow-sm transition-all">
                                            {{ $rep->status === 'reviewed' ? 'Update' : 'Review' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No periodic weekly or monthly reports submitted yet by proctors.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Proctor Submissions Archive -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-4 sm:p-5 border-b border-white/[0.06] flex items-center justify-between">
                <h3 class="text-xs font-semibold text-white uppercase tracking-wider">
                    Curfew Submissions Archive ({{ $submissions->count() }} Submissions)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 uppercase text-[10px] font-semibold text-slate-400 tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Date</th>
                            <th class="px-5 py-3.5">Dormitory Hall</th>
                            <th class="px-5 py-3.5">Reporting Proctor</th>
                            <th class="px-5 py-3.5">Total Roster</th>
                            <th class="px-5 py-3.5">Present</th>
                            <th class="px-5 py-3.5">Absent</th>
                            <th class="px-5 py-3.5">On Leave</th>
                            <th class="px-5 py-3.5">Rate</th>
                            <th class="px-5 py-3.5">Submission Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($submissions as $sub)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 font-mono text-white font-medium">
                                    {{ $sub->date->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-emerald-400">
                                    {{ $sub->block->name }} ({{ $sub->block->block_code }})
                                </td>
                                <td class="px-5 py-3.5 text-slate-300">
                                    {{ $sub->proctor->name }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-300">{{ $sub->total_students }}</td>
                                <td class="px-5 py-3.5 text-emerald-400 font-semibold">{{ $sub->present_count }}</td>
                                <td class="px-5 py-3.5 text-rose-400 font-semibold">{{ $sub->absent_count }}</td>
                                <td class="px-5 py-3.5 text-amber-400 font-semibold">{{ $sub->excused_count }}</td>
                                <td class="px-5 py-3.5 font-mono font-bold text-emerald-400">{{ $sub->attendance_rate }}%</td>
                                <td class="px-5 py-3.5 text-slate-400 font-mono">
                                    {{ $sub->submitted_at->format('H:i, M d') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No curfew round submissions found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>