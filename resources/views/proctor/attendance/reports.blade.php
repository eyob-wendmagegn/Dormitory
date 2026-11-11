<x-app-layout title="Curfew Attendance Reports">
    @php
        $block = $reportSummary['block'];
        $trackedDays = $reportSummary['tracked_days_count'];
        $totalResidents = $reportSummary['total_residents'];
        $totalPresent = $reportSummary['total_present'];
        $totalAbsent = $reportSummary['total_absent'];
        $totalExcused = $reportSummary['total_excused'];
        $totalSick = $reportSummary['total_sick'];
        $attendanceRate = $reportSummary['attendance_rate'];
        $dailyBreakdown = $reportSummary['daily_breakdown'];
        $frequentAbsentees = $reportSummary['frequent_absentees'];
        $submittedReport = $reportSummary['submitted_report'];
        $isSubmitted = $reportSummary['is_submitted'];
    @endphp

    <div class="space-y-6" x-data="{ submitModalOpen: false, activeTab: 'attendance' }">
        <!-- 1. Top Header with Quick Preset Buttons -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="text-xs uppercase font-semibold tracking-wider text-slate-400">
                        Official Dormitory Aggregates
                    </span>
                    @if($isSubmitted)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Report Submitted to Manager
                        </span>
                    @endif
                </div>

                <h2 class="text-2xl font-bold tracking-tight text-white">
                    {{ ucfirst($reportType) }} Curfew Attendance Report &bull; {{ $block->name }} ({{ $block->block_code }})
                </h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">
                    Continuous data aggregation from nightly proctor rounds. Review overall summary and frequent absentees before submitting to Proctor Manager.
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Generate Weekly Report Shortcut -->
                <a href="{{ route('proctor.attendance.reports', ['block_id' => $selectedBlockId, 'type' => 'weekly', 'start_date' => \Carbon\Carbon::today()->subDays(6)->toDateString(), 'end_date' => \Carbon\Carbon::today()->toDateString()]) }}"
                   class="px-4 py-2.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-2 {{ $reportType === 'weekly' ? 'bg-white/10 text-white border border-white/10 shadow-sm' : 'bg-white/[0.05] text-slate-300 hover:text-white hover:bg-white/[0.09] border border-white/10' }}">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Weekly Report</span>
                </a>

                <!-- Generate Monthly Report Shortcut -->
                <a href="{{ route('proctor.attendance.reports', ['block_id' => $selectedBlockId, 'type' => 'monthly', 'start_date' => \Carbon\Carbon::today()->startOfMonth()->toDateString(), 'end_date' => \Carbon\Carbon::today()->toDateString()]) }}"
                   class="px-4 py-2.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-2 {{ $reportType === 'monthly' ? 'bg-white/10 text-white border border-white/10 shadow-sm' : 'bg-white/[0.05] text-slate-300 hover:text-white hover:bg-white/[0.09] border border-white/10' }}">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Monthly Report</span>
                </a>

                <button onclick="window.print()" class="p-2.5 rounded-xl bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 text-slate-300 hover:text-white transition-colors" title="Print Report">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </button>
            </div>
        </div>

        <!-- 2. Controls & Period Selection Bar -->
        <div class="p-5 rounded-2xl bg-[#111726]/70 border border-white/[0.06]">
            <form method="GET" action="{{ route('proctor.attendance.reports') }}" class="flex flex-wrap items-end gap-3">
                @if($managedBlocks->count() > 1)
                    <div>
                        <label class="block text-[10px] uppercase font-semibold tracking-wider text-slate-400 mb-1">Block</label>
                        <select name="block_id" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 text-xs font-medium text-white rounded-xl px-3.5 py-2.5 focus:outline-none transition-all">
                            @foreach($managedBlocks as $b)
                                <option value="{{ $b->id }}" {{ $b->id == $selectedBlockId ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->block_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="block_id" value="{{ $selectedBlockId }}">
                @endif

                <div>
                    <label class="block text-[10px] uppercase font-semibold tracking-wider text-slate-400 mb-1">Report Mode</label>
                    <select name="type" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 text-xs font-medium text-white rounded-xl px-3.5 py-2.5 focus:outline-none transition-all">
                        <option value="weekly" {{ $reportType === 'weekly' ? 'selected' : '' }}>Weekly Report (Last 7 Days)</option>
                        <option value="monthly" {{ $reportType === 'monthly' ? 'selected' : '' }}>Monthly Report (Current Month)</option>
                        <option value="custom" {{ $reportType === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-semibold tracking-wider text-slate-400 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 text-xs font-medium text-white rounded-xl px-3.5 py-2.5 focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-semibold tracking-wider text-slate-400 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 text-xs font-medium text-white rounded-xl px-3.5 py-2.5 focus:outline-none transition-all">
                </div>

                <div>
                    <button type="submit" class="bg-white hover:bg-slate-100 text-slate-950 font-semibold px-4 py-2.5 text-xs rounded-xl shadow-md transition-all active:scale-[0.98]">
                        Update Report
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. Overall Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400 block mb-0.5">Tracked Days</span>
                <span class="text-2xl font-bold font-mono text-white block">{{ $trackedDays }} Days</span>
                <span class="text-[10px] text-slate-400 font-mono mt-0.5 block">{{ $startDate }} &rarr; {{ $endDate }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400 block mb-0.5">Block Residents</span>
                <span class="text-2xl font-bold font-mono text-white block">{{ $totalResidents }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Active Allocations</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-emerald-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Total Present
                </span>
                <span class="text-2xl font-bold font-mono text-emerald-400 block">{{ $totalPresent }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Logged in Period</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-rose-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Total Absences
                </span>
                <span class="text-2xl font-bold font-mono text-rose-400 block">{{ $totalAbsent }}</span>
                <span class="text-[10px] text-rose-400/80 mt-0.5 block">Curfew Violations</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-amber-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Gate Pass / Leave
                </span>
                <span class="text-2xl font-bold font-mono text-amber-400 block">{{ $totalExcused }}</span>
                <span class="text-[10px] text-amber-400/80 mt-0.5 block">Approved Leaves</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-cyan-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> Sick / Medical
                </span>
                <span class="text-2xl font-bold font-mono text-cyan-400 block">{{ $totalSick }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Clinic Verified</span>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-emerald-400 block mb-0.5">Attendance Rate</span>
                <span class="text-2xl font-bold font-mono text-emerald-400 block">{{ $attendanceRate }}%</span>
                <span class="text-[10px] text-emerald-400/80 font-mono mt-0.5 block">{{ count($frequentAbsentees) }} Frequent Absentees</span>
            </div>
        </div>

        <!-- 4. Report Submission to Proctor Manager Action Bar -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xl shadow-black/25">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-semibold tracking-wider text-slate-400">Manager Submission Workflow</span>
                    @if($isSubmitted)
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
                            Submitted on {{ $submittedReport->submitted_at?->format('H:i, M d, Y') }}
                        </span>
                    @endif
                </div>
                <h3 class="text-lg font-bold text-white tracking-tight">
                    {{ $isSubmitted ? 'Report Submitted to Proctor Manager' : 'Submit ' . ucfirst($reportType) . ' Report to Proctor Manager' }}
                </h3>
                <p class="text-xs text-slate-400 mt-1 max-w-xl leading-relaxed">
                    {{ $isSubmitted
                        ? 'This aggregated report has been transmitted to the Proctor Manager. Status: ' . strtoupper($submittedReport->status) . '.'
                        : 'Review the overall summary above and the frequent absentees table below. Upon your confirmation, the report will be dispatched to the Proctor Manager.'
                    }}
                </p>
                @if($isSubmitted && $submittedReport->notes)
                    <p class="text-xs text-slate-300 mt-1.5 font-mono">
                        Notes: {{ $submittedReport->notes }}
                    </p>
                @endif
                @if($isSubmitted && $submittedReport->manager_remarks)
                    <div class="mt-2.5 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300">
                        <span class="font-semibold">Manager Remarks:</span> {{ $submittedReport->manager_remarks }}
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button type="button"
                        @click="submitModalOpen = true"
                        class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $isSubmitted ? 'Re-Submit Updated Report' : 'Submit Report to Proctor Manager' }}</span>
                </button>
            </div>
        </div>

        <!-- 5. Dedicated Summary Table: Frequent Absentees List -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="p-5 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span>Dedicated Frequent Absentees List</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                {{ count($frequentAbsentees) }} Students Flagged
                            </span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Resident students who have accumulated 2 or more curfew absences during the selected report window ({{ $startDate }} to {{ $endDate }}).
                        </p>
                    </div>
                </div>

                <div class="text-xs text-slate-400 font-mono">
                    Threshold: &ge; 2 Absences
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Student Name &amp; ID</th>
                            <th class="px-5 py-4">Room Number</th>
                            <th class="px-5 py-4">Department</th>
                            <th class="px-5 py-4 text-center">Total Absences</th>
                            <th class="px-5 py-4 text-center">Unexcused Absences</th>
                            <th class="px-5 py-4">Most Recent Absence</th>
                            <th class="px-5 py-4">Violation Severity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($frequentAbsentees as $fa)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $fa['avatar_url'] }}" alt="{{ $fa['name'] }}"
                                             class="w-9 h-9 rounded-xl object-cover border {{ $fa['has_photo'] ? 'border-emerald-500/50' : 'border-white/10' }}">
                                        <div>
                                            <div class="font-semibold text-white">{{ $fa['name'] }}</div>
                                            <div class="text-xs font-mono text-slate-400">{{ $fa['university_id'] }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 font-mono font-medium text-white">
                                    Room {{ $fa['room_number'] }}
                                    <span class="text-[10px] text-slate-400 block font-normal">Floor {{ $fa['floor'] }}</span>
                                </td>

                                <td class="px-5 py-4 text-slate-200">
                                    {{ $fa['department'] }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 inline-flex items-center gap-1 shadow-sm">
                                        {{ $fa['total_absences'] }}x
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center font-bold text-rose-400 font-mono">
                                    {{ $fa['unexcused_absences'] }}
                                </td>

                                <td class="px-5 py-4 text-slate-300 font-mono">
                                    {{ $fa['last_absence_date'] ?? 'N/A' }}
                                    <span class="text-[10px] text-slate-400 block font-sans">
                                        Dates: {{ implode(', ', $fa['absence_dates']) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    @if($fa['is_chronic'])
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-rose-500/15 text-rose-300 border border-rose-500/30 flex items-center gap-1.5 w-max shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-ping"></span>
                                            Critical (3+ Absences)
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/15 text-amber-300 border border-amber-500/30 flex items-center gap-1.5 w-max">
                                            Warning (2 Absences)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-xs text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-8 h-8 text-emerald-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="font-semibold text-white">No Frequent Absentees Detected</span>
                                        <span class="text-xs text-slate-400 mt-1">All residents have maintained compliant curfew attendance during this period.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6. Daily Breakdown Table (Aggregated by Day) -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300">
                    Daily Curfew Breakdown ({{ count($dailyBreakdown) }} Tracked Days)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Date</th>
                            <th class="px-5 py-4">Total Checked</th>
                            <th class="px-5 py-4">Present</th>
                            <th class="px-5 py-4">Absent</th>
                            <th class="px-5 py-4">Gate Pass / Leave</th>
                            <th class="px-5 py-4">Sick</th>
                            <th class="px-5 py-4">Daily Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($dailyBreakdown as $db)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 font-semibold text-white font-mono">
                                    {{ \Carbon\Carbon::parse($db['date'])->format('D, M d, Y') }}
                                </td>
                                <td class="px-5 py-4 text-white font-medium">{{ $db['total'] }}</td>
                                <td class="px-5 py-4 text-emerald-400 font-semibold">{{ $db['present'] }}</td>
                                <td class="px-5 py-4 text-rose-400 font-semibold">{{ $db['absent'] }}</td>
                                <td class="px-5 py-4 text-amber-400 font-semibold">{{ $db['excused'] }}</td>
                                <td class="px-5 py-4 text-cyan-400 font-semibold">{{ $db['sick'] }}</td>
                                <td class="px-5 py-4 font-mono font-semibold text-emerald-400">
                                    {{ $db['rate'] }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-xs text-slate-400">
                                    No attendance records logged for this period. Use "Curfew Rounds" to log nightly attendance.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 7. Previous Submitted Reports Archive -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300">
                    Submitted Reports Archive to Proctor Manager
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Report ID</th>
                            <th class="px-5 py-4">Type</th>
                            <th class="px-5 py-4">Block</th>
                            <th class="px-5 py-4">Period</th>
                            <th class="px-5 py-4">Tracked Days</th>
                            <th class="px-5 py-4">Absences</th>
                            <th class="px-5 py-4">Frequent Absentees</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Submitted At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($submittedReports as $sr)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 font-mono font-bold text-white">#REP-{{ $sr->id }}</td>
                                <td class="px-5 py-4 font-semibold uppercase text-emerald-400">{{ $sr->report_type }}</td>
                                <td class="px-5 py-4 text-white font-medium">{{ $sr->block->block_code }}</td>
                                <td class="px-5 py-4 font-mono text-slate-300 text-xs">
                                    {{ $sr->start_date->format('M d') }} &rarr; {{ $sr->end_date->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4 text-slate-300">{{ $sr->tracked_days_count }} Days</td>
                                <td class="px-5 py-4 text-rose-400 font-semibold">{{ $sr->total_absent }}</td>
                                <td class="px-5 py-4 text-amber-300 font-semibold">{{ $sr->frequent_absentees_count }}</td>
                                <td class="px-5 py-4">
                                    @if($sr->status === 'reviewed')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Reviewed
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-medium uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Submitted
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-400 font-mono text-xs">
                                    {{ $sr->submitted_at->format('H:i, M d') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-xs text-slate-400">
                                    No periodic reports submitted yet. Use "Submit Report to Proctor Manager" above when ready.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit to Proctor Manager Confirmation Modal -->
        <div x-show="submitModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
             style="display: none;">
            <div @click.outside="submitModalOpen = false"
                 class="w-full max-w-lg rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Confirm Report Submission to Proctor Manager</h3>
                    </div>
                    <button type="button" @click="submitModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                </div>

                <!-- Review Checklist -->
                <div class="grid grid-cols-3 gap-2.5 text-center text-xs">
                    <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] text-slate-400 uppercase block font-semibold mb-0.5">Period</span>
                        <span class="font-bold text-white block">{{ ucfirst($reportType) }}</span>
                        <span class="text-[10px] text-slate-400 font-mono mt-0.5 block">{{ $trackedDays }} Days</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] text-slate-400 uppercase block font-semibold mb-0.5">Total Absences</span>
                        <span class="font-bold text-rose-400 text-base font-mono block">{{ $totalAbsent }}</span>
                        <span class="text-[10px] text-rose-400/80 mt-0.5 block">{{ count($frequentAbsentees) }} Frequent</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] text-slate-400 uppercase block font-semibold mb-0.5">Rate</span>
                        <span class="font-bold text-emerald-400 text-base font-mono block">{{ $attendanceRate }}%</span>
                        <span class="text-[10px] text-emerald-400/80 mt-0.5 block">{{ $totalPresent }} Present</span>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300">
                    <p class="font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Official Submission Details:
                    </p>
                    <p class="mt-1.5 text-amber-400/80 leading-relaxed">
                        This aggregated {{ $reportType }} report covering {{ $startDate }} to {{ $endDate }} will be officially transmitted to the Proctor Manager dashboard and logged in system audit.
                    </p>
                </div>

                <form method="POST" action="{{ route('proctor.attendance.reports.submit') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="block_id" value="{{ $selectedBlockId }}">
                    <input type="hidden" name="report_type" value="{{ $reportType }}">
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Proctor Summary Notes &amp; Observations</label>
                        <textarea name="notes" rows="3" placeholder="e.g. Conducted nightly checks across all rooms in Block {{ $block->block_code }}. Two students cited for repeated curfew violations without approved gate pass."
                                  class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                        <button type="button" @click="submitModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] rounded-xl border border-white/10 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 text-xs rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                            Confirm &amp; Transmit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
