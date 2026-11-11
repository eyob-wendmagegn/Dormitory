<x-app-layout title="My Attendance Records">
    <div class="space-y-6">
        <!-- Top Banner -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 mb-1 block">Resident Personal Attendance Portal</span>
                <h2 class="text-2xl font-bold tracking-tight text-white">My Attendance History</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">
                    @if($allocation)
                        Allocated in <strong class="text-white">{{ $allocation->room->block->name }} ({{ $allocation->room->block->block_code }})</strong> &bull; Room <strong class="text-white font-mono">{{ $allocation->room->room_number }}</strong>
                    @else
                        No active room allocation recorded.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('student.leaves.index') }}" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Request Gate Pass / Leave</span>
                </a>
            </div>
        </div>

        <!-- Warning Banner if flagged for multiple absences -->
        @if($summary['is_flagged'])
            <div class="p-5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-200 flex items-start gap-3.5 shadow-lg">
                <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="text-xs">
                    <span class="font-bold text-white block text-sm">Attendance Alert: Multiple Unexcused Absences Detected</span>
                    <p class="mt-0.5 text-rose-300/90 leading-relaxed">
                        You have accumulated <strong class="text-white">{{ $summary['absent_days'] }} absences</strong> during dormitory curfew inspections. Per university residential regulations, please report to your Block Proctor or the Proctor Office to resolve any disciplinary flags.
                    </p>
                </div>
            </div>
        @endif

        <!-- Active Approved Gate Pass Banner (if active today) -->
        @if($activeLeave)
            <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-200 flex items-center justify-between gap-4 shadow-md">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-300 font-bold text-xs">
                        PASS
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white">Active Authorized Gate Pass (#LR-{{ $activeLeave->id }})</div>
                        <div class="text-xs text-amber-300/80 mt-0.5">
                            Valid from {{ $activeLeave->start_date->format('M d') }} through {{ $activeLeave->end_date->format('M d, Y') }} &bull; Destination: {{ $activeLeave->destination }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('student.leaves.index') }}" class="px-3.5 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 text-xs font-semibold border border-amber-500/30 transition-all whitespace-nowrap">
                    View Gate Pass &rarr;
                </a>
            </div>
        @endif

        <!-- 5 KPI Overview Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400 block mb-0.5">Compliance Rate</span>
                <span class="text-2xl sm:text-3xl font-bold text-emerald-400 block font-mono">
                    {{ $summary['attendance_rate'] }}%
                </span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Overall Attendance</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-emerald-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Present
                </span>
                <span class="text-2xl sm:text-3xl font-bold font-mono text-emerald-400 block">
                    {{ $summary['present_days'] }}
                </span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Nights Verified</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-rose-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Absent
                </span>
                <span class="text-2xl sm:text-3xl font-bold font-mono text-rose-400 block">
                    {{ $summary['absent_days'] }}
                </span>
                <span class="text-[10px] text-rose-400/80 mt-0.5 block">Unexcused</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-amber-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> On Leave
                </span>
                <span class="text-2xl sm:text-3xl font-bold font-mono text-amber-400 block">
                    {{ $summary['excused_days'] }}
                </span>
                <span class="text-[10px] text-amber-400/80 mt-0.5 block">Approved Gate Pass</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-cyan-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> Sick / Clinic
                </span>
                <span class="text-2xl sm:text-3xl font-bold font-mono text-cyan-400 block">
                    {{ $summary['sick_days'] }}
                </span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Medical Excuse</span>
            </div>
        </div>

        <!-- Attendance History Timeline / Records Table -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Curfew Attendance Log</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Record of presence during nightly dorm curfew rounds.</p>
                </div>
                <span class="text-xs font-mono text-slate-400 bg-white/[0.05] px-3 py-1 rounded-full border border-white/10">
                    {{ $summary['total_days'] }} Inspection(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Date</th>
                            <th class="px-5 py-4">Curfew Status</th>
                            <th class="px-5 py-4">Dorm Location</th>
                            <th class="px-5 py-4">Recorded By</th>
                            <th class="px-5 py-4">Timestamp</th>
                            <th class="px-5 py-4">Remarks / Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($summary['recent_records'] as $record)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 font-mono text-white font-medium">
                                    {{ $record->date->format('M d, Y (D)') }}
                                </td>
                                <td class="px-5 py-4">
                                    @if($record->status === 'present')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Present
                                        </span>
                                    @elseif($record->status === 'absent')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20 inline-flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Absent
                                        </span>
                                    @elseif($record->status === 'excused')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 inline-flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            On Leave
                                        </span>
                                    @elseif($record->status === 'sick')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 inline-flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            Sick / Clinic
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-medium text-white">{{ $record->block?->block_code }}</span> - Rm {{ $record->room?->room_number }}
                                </td>
                                <td class="px-5 py-4 text-slate-200 font-medium">
                                    {{ $record->proctor?->name ?? 'System Automated' }}
                                </td>
                                <td class="px-5 py-4 text-slate-400 font-mono text-xs">
                                    {{ $record->recorded_at?->format('H:i, M d') ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-4">
                                    @if($record->remarks)
                                        <span class="text-white">{{ $record->remarks }}</span>
                                    @else
                                        <span class="text-slate-500 italic">None</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-xs text-slate-400">
                                    <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    No curfew attendance records logged yet. Records are generated every evening between 9:00 PM and 10:00 PM.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
