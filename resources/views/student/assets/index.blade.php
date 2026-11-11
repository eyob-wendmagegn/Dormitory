<x-app-layout title="My Dormitory Assets & Clearance">
    <div class="space-y-6">
        <!-- Top Header Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-semibold tracking-wider text-slate-400">Asset Distribution & Clearance (Stage 2 & 3)</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                        Resident Portal
                    </span>
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-white">My Dormitory Assets & Clearance Status</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">
                    Student ID: <span class="text-white font-medium font-mono">{{ $student->university_id }}</span>
                    @if($allocation)
                        &bull; Block: <span class="text-white font-medium">{{ $allocation->room?->block?->name }} ({{ $allocation->room?->block?->block_code }})</span>
                        &bull; Room: <span class="text-emerald-400 font-mono font-medium">{{ $allocation->room?->room_number }}</span>
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('student.notices.index') }}" class="bg-white/[0.05] hover:bg-white/[0.09] text-slate-300 hover:text-white border border-white/10 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Block Notices</span>
                </a>
            </div>
        </div>

        @if($hasPendingConfirmation)
            <!-- STAGE 2: PENDING STUDENT CONFIRMATION CALLOUT -->
            <div class="p-6 rounded-3xl bg-emerald-500/10 border border-emerald-500/20 shadow-xl relative overflow-hidden">
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Action Required: Digital Handover Acknowledgment</span>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-white mb-1">Your Block Proctor Has Issued Dormitory Assets to You</h3>
                <p class="text-xs text-slate-300 mb-4 leading-relaxed">
                    Please inspect your received items (mattress and/or pillow) in your room and click "Confirm Receipt" below to acknowledge the physical handover.
                </p>

                <div class="space-y-3">
                    @foreach($issuedAssets->where('status', 'pending_confirmation') as $pendingItem)
                        <div class="p-4 rounded-2xl bg-[#121927]/90 border border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                                    ✓
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-white capitalize">{{ $pendingItem->quantity }}x {{ $pendingItem->item_type }}</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        Issued by Proctor <strong class="text-white">{{ $pendingItem->proctor?->name }}</strong> on {{ $pendingItem->issue_date?->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('student.assets.confirm', $pendingItem) }}">
                                @csrf
                                <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2 rounded-xl text-xs shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Confirm Receipt</span>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- STAGE 3: CLEARANCE STATUS HERO CARD -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border {{ $isEligibleForClearance ? 'border-emerald-500/30' : 'border-amber-500/30' }} shadow-xl shadow-black/25 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl {{ $isEligibleForClearance ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }} flex items-center justify-center text-2xl font-bold shrink-0 shadow-lg">
                        @if($isEligibleForClearance)
                            ✓
                        @else
                            !
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Academic Clearance Status</span>
                            @if($isEligibleForClearance)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Eligible for Clearance
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    Pending Return / In Custody
                                </span>
                            @endif
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-white mt-1">
                            @if($isEligibleForClearance)
                                Clearance Approved: All Dormitory Assets Returned
                            @else
                                Active Assets In Custody &bull; Return Required for Sign-Off
                            @endif
                        </h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                            @if($isEligibleForClearance)
                                You have successfully returned all issued items in satisfactory condition with zero outstanding penalty balance. Your dormitory clearance sign-off is cleared.
                            @else
                                You must return both your mattress and pillow to your Block Proctor before the end of the term to receive your final dormitory clearance sign-off.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="text-right shrink-0">
                    <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">Outstanding Penalty</span>
                    <span class="text-xl font-bold font-mono {{ $unpaidPenalties > 0 ? 'text-rose-400' : 'text-emerald-400' }} block">
                        ETB {{ number_format($unpaidPenalties, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Issued Assets List Table -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Issued Dormitory Asset Inventory</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Historical log of items handed over, digital confirmations, and return inspections</p>
                </div>
                <span class="text-xs font-mono text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20 font-medium">
                    {{ $issuedAssets->count() }} Items Logged
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Asset Item</th>
                            <th class="px-5 py-4">Issued By</th>
                            <th class="px-5 py-4">Issue Date</th>
                            <th class="px-5 py-4">Handover Status</th>
                            <th class="px-5 py-4">Condition Status</th>
                            <th class="px-5 py-4">Return Date</th>
                            <th class="px-5 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($issuedAssets as $as)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-white/[0.04] border border-white/10 flex items-center justify-center font-bold text-xs {{ $as->item_type === 'mattress' ? 'text-emerald-400' : 'text-cyan-400' }}">
                                            {{ substr(strtoupper($as->item_type), 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-semibold text-white capitalize block">{{ $as->item_type }}</span>
                                            <span class="text-[10px] text-slate-400">Qty: {{ $as->quantity }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-white block">{{ $as->proctor?->name ?? 'Block Proctor' }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">ID: {{ $as->proctor?->university_id }}</span>
                                </td>
                                <td class="px-5 py-4 font-mono text-slate-400">{{ $as->issue_date?->format('M d, Y') }}</td>
                                <td class="px-5 py-4">
                                    @if($as->status === 'confirmed')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5 w-fit">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Confirmed
                                        </span>
                                    @elseif($as->status === 'pending_confirmation')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center gap-1.5 w-fit">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                            Awaiting Confirmation
                                        </span>
                                    @elseif($as->status === 'returned')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center gap-1.5 w-fit">
                                            Returned
                                        </span>
                                    @elseif($as->status === 'damaged')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center gap-1.5 w-fit">
                                            Damaged (Penalty: ETB {{ number_format($as->penalty_charge, 2) }})
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center gap-1.5 w-fit">
                                            Missing (Penalty: ETB {{ number_format($as->penalty_charge, 2) }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 capitalize font-medium text-slate-200">
                                    {{ $as->condition_status ?: 'In Custody' }}
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-400">
                                    {{ $as->return_date ? $as->return_date->format('M d, Y') : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($as->status === 'pending_confirmation')
                                        <form method="POST" action="{{ route('student.assets.confirm', $as) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-xs shadow-sm transition-all active:scale-[0.98]">
                                                Confirm
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-500 font-mono">Logged</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    No dormitory assets currently issued to your account.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
