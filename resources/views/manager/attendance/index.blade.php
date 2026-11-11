<x-app-layout title="Curfew Monitoring & Daily Absentee List">
    <div class="space-y-6" x-data="{ customWarningModal: false, selectedStudentName: '', selectedStudentId: null }">
        <!-- Top Banner Console -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Chief Dormitory Administration Console</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        Curfew Window: {{ $monitoring['curfew']['window'] }}
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Daily Curfew Rounds & Absentee Monitoring</h2>
                <p class="text-xs text-slate-400 mt-0.5 max-w-xl">
                    Campus-wide oversight of nightly dorm room checks submitted by block proctors. View real-time absentees and dispatch automated warnings.
                </p>
            </div>

            <!-- Date, Block Filter & Export Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <form method="GET" action="{{ route(auth()->user()->isAdmin() ? 'admin.attendance.index' : 'manager.attendance.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 text-xs font-medium text-white rounded-xl px-3.5 py-2 transition-all">

                    <select name="block_id" onchange="this.form.submit()" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 text-xs font-medium text-white rounded-xl px-3.5 py-2 transition-all">
                        <option value="">All Dorm Blocks</option>
                        @foreach($blocks as $b)
                            <option value="{{ $b->id }}" {{ $blockId == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->block_code }})
                            </option>
                        @endforeach
                    </select>
                </form>

                <a href="{{ route('attendance.export_csv', ['date' => $date, 'type' => 'absentees']) }}" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- Campus-wide KPI Cards for Date -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-slate-400 block tracking-wider">Compliance Rate</span>
                <span class="text-2xl font-bold text-emerald-400 mt-1 block font-mono">{{ $monitoring['overall_rate'] }}%</span>
                <span class="text-[10px] text-slate-500">Present Residents</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-emerald-400 block flex items-center gap-1.5 tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Present
                </span>
                <span class="text-2xl font-bold text-white mt-1 block font-mono">{{ $monitoring['totals']['present'] }}</span>
                <span class="text-[10px] text-slate-500">In Dorms</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-rose-400 block flex items-center gap-1.5 tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Total Absent
                </span>
                <span class="text-2xl font-bold text-rose-400 mt-1 block font-mono">{{ $monitoring['totals']['absent'] }}</span>
                <span class="text-[10px] text-rose-400/80">Flagged Tonight</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-amber-400 block flex items-center gap-1.5 tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> On Leave
                </span>
                <span class="text-2xl font-bold text-amber-400 mt-1 block font-mono">{{ $monitoring['totals']['excused'] }}</span>
                <span class="text-[10px] text-amber-400/80">Authorized Passes</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-cyan-400 block flex items-center gap-1.5 tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> Sick / Clinic
                </span>
                <span class="text-2xl font-bold text-cyan-400 mt-1 block font-mono">{{ $monitoring['totals']['sick'] }}</span>
                <span class="text-[10px] text-slate-500">Admitted</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all">
                <span class="text-[10px] uppercase font-semibold text-slate-300 block flex items-center gap-1.5 tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-white/60"></span> Submissions
                </span>
                <span class="text-2xl font-bold text-white mt-1 block font-mono">
                    {{ $monitoring['totals']['submitted_blocks'] }} / {{ count($monitoring['blocks']) }}
                </span>
                <span class="text-[10px] text-slate-500">Halls Finalized</span>
            </div>
        </div>

        <!-- Curfew Status by Dormitory Block Grid -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25 p-5">
            <h3 class="text-xs font-semibold text-white uppercase tracking-wider mb-3">
                Curfew Rounds Submission Progress by Dorm Block ({{ $date }})
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($monitoring['blocks'] as $bStat)
                    <div class="p-3.5 rounded-2xl bg-[#0F1420]/70 border {{ $bStat['is_submitted'] ? 'border-emerald-500/30' : 'border-white/[0.06]' }} flex items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-white text-xs">{{ $bStat['block']->name }}</span>
                                <span class="text-[10px] font-mono text-emerald-400">({{ $bStat['block']->block_code }})</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                Proctor: <span class="text-white">{{ $bStat['proctor']?->name ?? 'Unsubmitted' }}</span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-2">
                                <span class="text-emerald-400 font-medium">{{ $bStat['present_count'] }} Present</span>
                                &bull;
                                <span class="text-rose-400 font-medium">{{ $bStat['absent_count'] }} Absent</span>
                                &bull;
                                <span class="text-amber-400 font-medium">{{ $bStat['excused_count'] }} Leave</span>
                            </div>
                        </div>

                        <div class="text-right">
                            @if($bStat['is_submitted'])
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 block mb-1">
                                    Submitted
                                </span>
                                <span class="text-[10px] text-slate-500 font-mono block">
                                    {{ $bStat['submission']->submitted_at->format('H:i') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-medium uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20 block">
                                    Pending
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Daily Absentee List Table -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-4 sm:p-5 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-400 animate-pulse"></span>
                        <h3 class="text-sm font-semibold text-white uppercase tracking-wider">
                            Daily Absentee List &bull; {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                        </h3>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Students recorded as absent during nightly dormitory curfew rounds across campus halls.
                    </p>
                </div>

                <!-- Bulk Notify Button -->
                @if($absentees->isNotEmpty())
                    <form method="POST" action="{{ route(auth()->user()->isAdmin() ? 'admin.attendance.notify_absent' : 'manager.attendance.notify_absent') }}" onsubmit="return confirm('Dispatch official attendance warning notifications to all absent students listed?')">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        @if($blockId)
                            <input type="hidden" name="block_id" value="{{ $blockId }}">
                        @endif
                        <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-white bg-rose-500/90 hover:bg-rose-500 rounded-xl transition-all shadow-md flex items-center gap-2 active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span>Broadcast Warning to All Absentees</span>
                        </button>
                    </form>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 uppercase text-[10px] font-semibold text-slate-400 tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Record ID</th>
                            <th class="px-5 py-3.5">Student Details</th>
                            <th class="px-5 py-3.5">Hall & Room</th>
                            <th class="px-5 py-3.5">Department & Batch</th>
                            <th class="px-5 py-3.5">Assigned Proctor</th>
                            <th class="px-5 py-3.5">Inspection Time</th>
                            <th class="px-5 py-3.5">Proctor Remarks</th>
                            <th class="px-5 py-3.5 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($absentees as $att)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 font-mono text-emerald-400 font-bold">
                                    #ATT-{{ $att->id }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $att->student?->avatar_url }}" alt="{{ $att->student?->name }}" class="w-8 h-8 rounded-lg object-cover border border-white/10">
                                        <div>
                                            <span class="block font-semibold text-white text-xs">{{ $att->student?->name ?? 'Unknown Student' }}</span>
                                            <span class="block text-[10px] text-slate-400 font-mono">{{ $att->student?->university_id }} &bull; {{ ucfirst($att->student?->gender ?? '') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-white">
                                    {{ $att->block?->name }} ({{ $att->block?->block_code }}) &bull; Rm {{ $att->room?->room_number }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="text-white font-medium">{{ $att->student?->department ?? 'General' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $att->student?->academic_year_label ?? 'Undergrad' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-slate-300">
                                    {{ $att->proctor?->name ?? 'Block Proctor' }}
                                </td>
                                <td class="px-5 py-3.5 font-mono text-slate-400">
                                    {{ $att->recorded_at?->format('H:i:s, M d') ?? 'Curfew Round' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($att->remarks)
                                        <span class="text-rose-400 font-medium">{{ $att->remarks }}</span>
                                    @else
                                        <span class="text-slate-500 italic">Unexcused</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        ABSENT
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-xs text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mx-auto mb-2.5">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <p class="font-semibold text-white text-sm">No absentees logged for this date</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Either all active residents were accounted for or rounds are awaiting final submission.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>