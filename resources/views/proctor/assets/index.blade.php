<x-app-layout title="Asset Distribution & Return Management">
    <div class="space-y-6" x-data="{ issueModal: false, returnModal: false, activeReturnItem: null, rejectModal: false, activeRejectTransfer: null }">
        <!-- Top Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-semibold tracking-wider text-slate-400">Asset Distribution & Clearance (Stage 1, 2 & 3)</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                        Managing Block: {{ $block?->block_code ?? 'All Assigned' }}
                    </span>
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-white">Dormitory Asset Operations & Stock Control</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">
                    Accept warehouse transfers, issue mattresses and pillows to residents upon arrival, inspect returned items, and manage clearance eligibility.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" @click="issueModal = true" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Issue Asset to Student</span>
                </button>
                <a href="{{ route('proctor.assets.clearance') }}" class="bg-white/[0.05] hover:bg-white/[0.09] text-slate-300 hover:text-white border border-white/10 px-4 py-2.5 rounded-xl text-sm font-medium transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Student Clearance Roster</span>
                </a>
            </div>
        </div>

        @if($pendingTransfers->isNotEmpty())
            <!-- STAGE 1 ALERT: Pending Handover Transfers from Proctor Manager -->
            <div class="p-6 rounded-3xl bg-emerald-500/10 border border-emerald-500/20 shadow-xl relative overflow-hidden">
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Stage 1: Pending Handover Transfers from Warehouse</span>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-white mb-3">Warehouse Asset Shipments Awaiting Your Physical Verification</h3>

                <div class="space-y-3">
                    @foreach($pendingTransfers as $pt)
                        <div class="p-4 rounded-2xl bg-[#121927]/90 border border-white/[0.06] flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-white/[0.04] border border-white/10 flex items-center justify-center font-bold text-sm text-emerald-400">
                                    #{{ $pt->id }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-white text-sm capitalize">{{ $pt->assigned_quantity }}x {{ $pt->item_type }}(s)</span>
                                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-400 font-mono border border-amber-500/20">
                                            Transfer Initiated by {{ $pt->manager?->name ?? 'Proctor Manager' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">
                                        Transferred on: <span class="text-white">{{ $pt->transfer_date?->format('M d, Y') }}</span>
                                        @if($pt->notes) &bull; Notes: <span class="italic text-slate-300">{{ $pt->notes }}</span>@endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2.5 shrink-0">
                                <form method="POST" action="{{ route('proctor.assets.confirm_transfer', $pt) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="accept" />
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2 rounded-xl text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Accept Handover</span>
                                    </button>
                                </form>
                                <button type="button" @click="activeRejectTransfer = {{ $pt->id }}; rejectModal = true;" class="px-3.5 py-2 text-xs font-medium text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 rounded-xl transition-all">
                                    Decline / Reject
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Proctor's Current Stock Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Mattress Stock -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-emerald-400">Proctor Current Stock</span>
                            <h3 class="text-base font-bold text-white">Mattress Inventory</h3>
                        </div>
                    </div>
                    <span class="text-xs font-medium font-mono px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        {{ $currentStock['mattress_available'] }} Available to Issue
                    </span>
                </div>

                <div class="grid grid-cols-4 gap-2.5 text-center">
                    <div class="p-3 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">Confirmed</span>
                        <span class="text-xl font-bold text-white font-mono block">{{ $currentStock['mattress_total'] }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">Issued Active</span>
                        <span class="text-xl font-bold text-amber-400 font-mono block">{{ $currentStock['mattress_issued'] }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">In Stock</span>
                        <span class="text-xl font-bold text-emerald-400 font-mono block">{{ $currentStock['mattress_available'] }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">Returned</span>
                        <span class="text-xl font-bold text-cyan-400 font-mono block">{{ $currentStock['mattress_returned'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Pillow Stock -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-cyan-400">Proctor Current Stock</span>
                            <h3 class="text-base font-bold text-white">Pillow Inventory</h3>
                        </div>
                    </div>
                    <span class="text-xs font-medium font-mono px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                        {{ $currentStock['pillow_available'] }} Available to Issue
                    </span>
                </div>

                <div class="grid grid-cols-4 gap-2.5 text-center">
                    <div class="p-3 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">Confirmed</span>
                        <span class="text-xl font-bold text-white font-mono block">{{ $currentStock['pillow_total'] }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">Issued Active</span>
                        <span class="text-xl font-bold text-amber-400 font-mono block">{{ $currentStock['pillow_issued'] }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">In Stock</span>
                        <span class="text-xl font-bold text-cyan-400 font-mono block">{{ $currentStock['pillow_available'] }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">Returned</span>
                        <span class="text-xl font-bold text-emerald-400 font-mono block">{{ $currentStock['pillow_returned'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stage 2 & 3: Student Asset Issuance and Return Inspections -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="p-5 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Student Asset Issuances & Return Inspections</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Digital handovers, student confirmations, condition checks, and end-of-semester returns</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('proctor.assets.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ !$statusFilter ? 'bg-white/10 text-white border border-white/10' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' }}">All</a>
                    <a href="{{ route('proctor.assets.index', ['status' => 'pending_confirmation']) }}" class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ $statusFilter === 'pending_confirmation' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' }}">Awaiting Student</a>
                    <a href="{{ route('proctor.assets.index', ['status' => 'confirmed']) }}" class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ $statusFilter === 'confirmed' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' }}">Active in Dorm</a>
                    <a href="{{ route('proctor.assets.index', ['status' => 'returned']) }}" class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ $statusFilter === 'returned' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' }}">Returned</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Student</th>
                            <th class="px-5 py-4">Room</th>
                            <th class="px-5 py-4">Item</th>
                            <th class="px-5 py-4">Issue Date</th>
                            <th class="px-5 py-4">Handover Status</th>
                            <th class="px-5 py-4">Condition Status</th>
                            <th class="px-5 py-4">Return Date</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($studentIssues as $iss)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $iss->student?->avatar_url }}" alt="{{ $iss->student?->name }}" class="w-8 h-8 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <span class="font-semibold text-white block">{{ $iss->student?->name ?? 'Unknown' }}</span>
                                            <span class="text-[10px] text-slate-400">ID: {{ $iss->student?->university_id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-white">
                                    {{ $iss->room?->room_number ?? '—' }} ({{ $iss->block?->block_code ?? '' }})
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-semibold capitalize {{ $iss->item_type === 'mattress' ? 'text-emerald-400' : 'text-cyan-400' }}">
                                        {{ $iss->item_type }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-mono text-slate-400">{{ $iss->issue_date?->format('M d, Y') }}</td>
                                <td class="px-5 py-4">
                                    @if($iss->status === 'confirmed')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5 w-fit">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Confirmed</span>
                                        </span>
                                    @elseif($iss->status === 'pending_confirmation')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center gap-1.5 w-fit">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                            <span>Awaiting Confirmation</span>
                                        </span>
                                    @elseif($iss->status === 'returned')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center gap-1.5 w-fit">
                                            <span>Returned</span>
                                        </span>
                                    @elseif($iss->status === 'damaged')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center gap-1.5 w-fit">
                                            <span>Damaged (ETB {{ number_format($iss->penalty_charge, 2) }})</span>
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center gap-1.5 w-fit">
                                            <span>Missing (ETB {{ number_format($iss->penalty_charge, 2) }})</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 capitalize font-medium text-slate-200">
                                    {{ $iss->condition_status ?: 'In Use' }}
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-400">
                                    {{ $iss->return_date ? $iss->return_date->format('M d, Y') : 'Active' }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if(in_array($iss->status, ['pending_confirmation', 'confirmed']))
                                        <button type="button" @click="activeReturnItem = {{ $iss->id }}; returnModal = true;" class="text-xs font-semibold text-slate-950 bg-white hover:bg-slate-100 px-3.5 py-1.5 rounded-xl shadow-md transition-all active:scale-[0.98]">
                                            Process Return
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-500 italic">Archived</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                    No student asset records logged.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($studentIssues->hasPages())
                <div class="p-4 border-t border-white/[0.06]">
                    {{ $studentIssues->links() }}
                </div>
            @endif
        </div>

        <!-- 1. Issue Asset to Student Modal -->
        <div x-show="issueModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
             style="display: none;">
            <div @click.outside="issueModal = false" class="w-full max-w-lg rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6">
                <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                    <div>
                        <h3 class="text-base font-bold text-white">Stage 2: Digital Handover to Student</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Issue mattress and pillow from your allocated stock</p>
                    </div>
                    <button type="button" @click="issueModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('proctor.assets.issue') }}" class="space-y-4 mt-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Select Student Resident</label>
                        <select name="student_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition-all">
                            <option value="">-- Choose Resident in Block {{ $block?->block_code }} --</option>
                            @foreach($blockStudents as $st)
                                <option value="{{ $st->id }}">{{ $st->name }} (ID: {{ $st->university_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Select Items to Hand Over</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3.5 rounded-xl bg-[#0F1420] border border-white/[0.08] cursor-pointer hover:border-emerald-500/50 transition-all">
                                <input type="checkbox" name="items[]" value="mattress" checked class="w-4 h-4 rounded bg-[#172134] border-white/10 text-emerald-500 focus:ring-emerald-500/20" />
                                <div>
                                    <span class="text-xs font-semibold text-white block">Mattress (1x)</span>
                                    <span class="text-[10px] text-slate-400">In stock: {{ $currentStock['mattress_available'] }}</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-xl bg-[#0F1420] border border-white/[0.08] cursor-pointer hover:border-cyan-500/50 transition-all">
                                <input type="checkbox" name="items[]" value="pillow" checked class="w-4 h-4 rounded bg-[#172134] border-white/10 text-cyan-500 focus:ring-cyan-500/20" />
                                <div>
                                    <span class="text-xs font-semibold text-white block">Pillow (1x)</span>
                                    <span class="text-[10px] text-slate-400">In stock: {{ $currentStock['pillow_available'] }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Remarks / Asset Condition at Handover</label>
                        <textarea name="remarks" rows="2" placeholder="e.g. Issued in new condition during campus intake" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all"></textarea>
                    </div>

                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-400">
                        <strong>Student Confirmation Step:</strong> After clicking "Issue Asset", the items are logged and the resident can digitally verify custody on their resident portal.
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/[0.06]">
                        <button type="button" @click="issueModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] rounded-xl border border-white/10 transition-all">Cancel</button>
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 text-xs rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                            Issue Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Process Return & Inspection Modal -->
        <div x-show="returnModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
             style="display: none;">
            <div @click.outside="returnModal = false" class="w-full max-w-md rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6">
                <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                    <div>
                        <h3 class="text-base font-bold text-white">Stage 3: Inspect & Receive Return</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Verify physical condition for student clearance</p>
                    </div>
                    <button type="button" @click="returnModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                </div>

                <form :action="'{{ url('/proctor/assets/issues') }}/' + activeReturnItem + '/return'" method="POST" class="space-y-4 mt-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Inspected Physical Condition</label>
                        <select name="condition_status" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition-all">
                            <option value="good">Return Received - Intact / Undamaged (Eligible for Clearance)</option>
                            <option value="damaged">Damaged (Requires Replacement Charge)</option>
                            <option value="missing">Missing / Not Returned (Requires Full Replacement Penalty)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Penalty Charge (ETB, If Damaged/Lost)</label>
                        <input type="number" step="0.01" name="penalty_charge" placeholder="Leave empty for standard rate (ETB 500-1200)" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Inspection Remarks</label>
                        <textarea name="remarks" rows="2" placeholder="e.g. Both items returned clean, approved for clearance." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/[0.06]">
                        <button type="button" @click="returnModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] rounded-xl border border-white/10 transition-all">Cancel</button>
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 text-xs rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                            Confirm Return
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Reject Handover Modal -->
        <div x-show="rejectModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
             style="display: none;">
            <div @click.outside="rejectModal = false" class="w-full max-w-md rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6">
                <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                    <h3 class="text-base font-bold text-white">Decline Handover Transfer</h3>
                    <button type="button" @click="rejectModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                </div>

                <form :action="'{{ url('/proctor/assets/transfers') }}/' + activeRejectTransfer + '/confirm'" method="POST" class="space-y-4 mt-5">
                    @csrf
                    <input type="hidden" name="action" value="reject" />
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Reason for Rejection</label>
                        <textarea name="rejection_reason" required rows="3" placeholder="e.g. Physical count discrepancy: only 40 mattresses delivered instead of 50." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/[0.06]">
                        <button type="button" @click="rejectModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] rounded-xl border border-white/10 transition-all">Cancel</button>
                        <button type="submit" class="bg-rose-500 hover:bg-rose-400 text-white font-semibold px-5 py-2.5 text-xs rounded-xl shadow-lg shadow-rose-500/20 active:scale-[0.98] transition-all">
                            Reject Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
