<x-app-layout title="Student Dormitory Clearance Roster">
    <div class="space-y-6">
        <!-- Top Header & Print Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 print:border-none print:shadow-none">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-semibold tracking-wider text-slate-400">End of Academic Period Clearance</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                        Block {{ $block?->block_code ?? 'All Assigned' }}
                    </span>
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-white">Student Asset Return & Clearance Roster</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">
                    Automated clearance verification based on return of mattresses and pillows in satisfactory condition with zero unpaid penalty charges.
                </p>
            </div>

            <div class="flex items-center gap-2.5 print:hidden">
                <button type="button" onclick="window.print()" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Clearance List</span>
                </button>
                <a href="{{ route('proctor.assets.index') }}" class="bg-white/[0.05] hover:bg-white/[0.09] text-slate-300 hover:text-white border border-white/10 px-4 py-2.5 rounded-xl text-sm font-medium transition-all">
                    &larr; Back to Assets
                </a>
            </div>
        </div>

        <!-- Clearance Summary Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 print:hidden">
            <div class="p-5 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20">
                <span class="text-xs uppercase font-semibold text-slate-400 block mb-1">Total Block Residents</span>
                <span class="text-3xl font-bold text-white font-mono block">{{ count($clearanceData) }}</span>
                <span class="text-xs text-slate-500 mt-1 block">Assigned to Block {{ $block?->block_code }}</span>
            </div>

            <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-semibold text-emerald-400 block">Eligible for Clearance</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                </div>
                <span class="text-3xl font-bold text-emerald-400 font-mono block mt-1">{{ $totalEligible }}</span>
                <span class="text-xs text-emerald-400/80 mt-1 block">All assets returned & inspected</span>
            </div>

            <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-semibold text-amber-400 block">Clearance Blocked / Pending</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                </div>
                <span class="text-3xl font-bold text-amber-400 font-mono block mt-1">{{ $totalBlocked }}</span>
                <span class="text-xs text-amber-400/80 mt-1 block">Items active or unpaid penalty</span>
            </div>
        </div>

        <!-- Clearance Roster Table -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Official Resident Clearance Sign-Off Roster</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Woldia University Dormitory Administration &bull; Academic Clearance Certification</p>
                </div>
                <span class="text-xs font-mono text-cyan-400 bg-cyan-500/10 px-3 py-1 rounded-full border border-cyan-500/20 font-medium">
                    {{ now()->format('F Y') }} Intake
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Student Resident</th>
                            <th class="px-5 py-4">Room</th>
                            <th class="px-5 py-4 text-center">Mattress Status</th>
                            <th class="px-5 py-4 text-center">Pillow Status</th>
                            <th class="px-5 py-4 text-right">Outstanding Penalty</th>
                            <th class="px-5 py-4 text-center">Clearance Status</th>
                            <th class="px-5 py-4 text-center">Sign-Off Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($clearanceData as $row)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $row['student']->avatar_url }}" alt="{{ $row['student']->name }}" class="w-8 h-8 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <span class="font-semibold text-white block">{{ $row['student']->name }}</span>
                                            <span class="text-[10px] text-slate-400">ID: {{ $row['student']->university_id }} &bull; {{ $row['student']->academic_year_label ?? 'Student' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono font-medium text-white">
                                    {{ $row['room']?->room_number ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($row['mattress_issue'])
                                        @if($row['mattress_issue']->status === 'returned')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Returned Intact</span>
                                        @elseif($row['mattress_issue']->status === 'confirmed')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">In Custody</span>
                                        @elseif($row['mattress_issue']->status === 'damaged')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">Damaged</span>
                                        @elseif($row['mattress_issue']->status === 'missing')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">Missing</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">Unconfirmed</span>
                                        @endif
                                    @else
                                        <span class="text-slate-500 italic">Not Issued</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($row['pillow_issue'])
                                        @if($row['pillow_issue']->status === 'returned')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Returned Intact</span>
                                        @elseif($row['pillow_issue']->status === 'confirmed')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">In Custody</span>
                                        @elseif($row['pillow_issue']->status === 'damaged')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">Damaged</span>
                                        @elseif($row['pillow_issue']->status === 'missing')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">Missing</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">Unconfirmed</span>
                                        @endif
                                    @else
                                        <span class="text-slate-500 italic">Not Issued</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-medium {{ $row['unpaid_penalties'] > 0 ? 'text-rose-400' : 'text-slate-400' }}">
                                    ETB {{ number_format($row['unpaid_penalties'], 2) }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($row['is_eligible'])
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1.5 shadow-sm">
                                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Eligible for Clearance
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Pending Asset Return
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center font-mono text-xs">
                                    @if($row['is_eligible'])
                                        <span class="text-emerald-400 font-semibold">CLEARED ✓</span>
                                    @else
                                        <span class="text-slate-500">HOLD</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    No residents allocated to this block.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
