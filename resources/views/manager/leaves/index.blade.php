<x-app-layout title="Campus-Wide Leave Requests & Gate Passes">
    <div class="space-y-6">
        <!-- Top Banner Console -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Campus Housing Governance</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Campus Gate Passes & Leave Oversight</h2>
                <p class="text-xs text-slate-400 mt-0.5 max-w-xl">
                    Centralized management and oversight of all campus departures, weekend leaves, and emergency gate passes across all dormitory blocks.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route(auth()->user()->isAdmin() ? 'admin.attendance.index' : 'manager.attendance.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Curfew Monitoring</span>
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards & Status Filters -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <a href="{{ route(auth()->user()->isAdmin() ? 'admin.leaves.index' : 'manager.leaves.index', ['status' => 'all']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 {{ $status === 'all' ? 'bg-[#172134]/90 border-emerald-500/40 shadow-lg' : 'bg-[#172134]/60 hover:bg-[#172134]/90 border-white/[0.05]' }}">
                <span class="text-xs font-semibold text-white uppercase tracking-wider">Total Submissions</span>
                <div class="text-2xl font-bold text-white mt-1 font-mono">{{ $counts['total'] }}</div>
                <div class="text-[11px] text-slate-400">All Leaves Recorded</div>
            </a>

            <a href="{{ route(auth()->user()->isAdmin() ? 'admin.leaves.index' : 'manager.leaves.index', ['status' => 'pending']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 {{ $status === 'pending' ? 'bg-[#172134]/90 border-amber-500/40 shadow-lg' : 'bg-[#172134]/60 hover:bg-[#172134]/90 border-white/[0.05]' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider">Pending Review</span>
                    <span class="w-2 h-2 rounded-full bg-amber-400 {{ $counts['pending'] > 0 ? 'animate-ping' : '' }}"></span>
                </div>
                <div class="text-2xl font-bold text-amber-400 mt-1 font-mono">{{ $counts['pending'] }}</div>
                <div class="text-[11px] text-slate-400">Awaiting Decision</div>
            </a>

            <a href="{{ route(auth()->user()->isAdmin() ? 'admin.leaves.index' : 'manager.leaves.index', ['status' => 'approved']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 {{ $status === 'approved' ? 'bg-[#172134]/90 border-emerald-500/40 shadow-lg' : 'bg-[#172134]/60 hover:bg-[#172134]/90 border-white/[0.05]' }}">
                <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Authorized</span>
                <div class="text-2xl font-bold text-emerald-400 mt-1 font-mono">{{ $counts['approved'] }}</div>
                <div class="text-[11px] text-slate-400">Approved Gate Passes</div>
            </a>

            <a href="{{ route(auth()->user()->isAdmin() ? 'admin.leaves.index' : 'manager.leaves.index', ['status' => 'rejected']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 {{ $status === 'rejected' ? 'bg-[#172134]/90 border-rose-500/40 shadow-lg' : 'bg-[#172134]/60 hover:bg-[#172134]/90 border-white/[0.05]' }}">
                <span class="text-xs font-semibold text-rose-400 uppercase tracking-wider">Declined</span>
                <div class="text-2xl font-bold text-rose-400 mt-1 font-mono">{{ $counts['rejected'] }}</div>
                <div class="text-[11px] text-slate-400">Rejected Passes</div>
            </a>
        </div>

        <!-- Leaves Table Card -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25"
             x-data="{
                 approveModalOpen: false,
                 rejectModalOpen: false,
                 activeLeaveId: null,
                 activeStudentName: '',
                 activeActionUrl: ''
             }">
            <div class="p-4 sm:p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Campus Leave Registry</h3>
                    <p class="text-xs text-slate-400">Official log of exit requests and authorizations.</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-white/[0.05] text-slate-300 border border-white/10">
                    {{ $leaves->total() }} Total
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 uppercase text-[10px] font-semibold text-slate-400 tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Ref #</th>
                            <th class="px-5 py-3.5">Student</th>
                            <th class="px-5 py-3.5">Dorm Hall</th>
                            <th class="px-5 py-3.5">Type</th>
                            <th class="px-5 py-3.5">Period & Destination</th>
                            <th class="px-5 py-3.5">Emergency Phone</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Reviewer</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($leaves as $leave)
                            @php
                                $student = $leave->student;
                                $alloc = $student?->activeAllocation;
                                $room = $alloc?->room;
                                $block = $room?->block;
                                $approveRoute = auth()->user()->isAdmin() ? route('admin.leaves.approve', $leave) : route('manager.leaves.approve', $leave);
                                $rejectRoute = auth()->user()->isAdmin() ? route('admin.leaves.reject', $leave) : route('manager.leaves.reject', $leave);
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 font-mono text-emerald-400 font-bold">
                                    #LR-{{ $leave->id }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $student?->avatar_url }}" alt="{{ $student?->name }}" class="w-8 h-8 rounded-lg object-cover border border-white/10">
                                        <div>
                                            <span class="block font-semibold text-white">{{ $student?->name }}</span>
                                            <span class="block text-[10px] text-slate-400 font-mono">{{ $student?->university_id }} &bull; {{ $student?->department ?? 'General' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-white">
                                    {{ $block?->name }} ({{ $block?->block_code }}) - Rm {{ $room?->room_number ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-white/[0.05] text-slate-300 border border-white/10">
                                        {{ $leave->type_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-white font-mono">
                                        {{ $leave->start_date->format('M d') }} &rarr; {{ $leave->end_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Dest: <span class="text-white">{{ $leave->destination }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 italic truncate max-w-xs" title="{{ $leave->reason }}">
                                        "{{ $leave->reason }}"
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-slate-300 text-[11px]">
                                    {{ $leave->emergency_phone ?: 'None' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($leave->status === 'approved')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Approved
                                        </span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            Declined
                                        </span>
                                    @elseif($leave->status === 'cancelled')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-white/[0.04] text-slate-400">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-300">
                                    {{ $leave->approver?->name ?? 'Awaiting' }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    @if($leave->isPending())
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button"
                                                    @click="activeLeaveId = {{ $leave->id }}; activeStudentName = '{{ addslashes($student?->name ?? '') }}'; activeActionUrl = '{{ $approveRoute }}'; approveModalOpen = true"
                                                    class="px-3 py-1.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-sm active:scale-[0.98]">
                                                Approve
                                            </button>
                                            <button type="button"
                                                    @click="activeLeaveId = {{ $leave->id }}; activeStudentName = '{{ addslashes($student?->name ?? '') }}'; activeActionUrl = '{{ $rejectRoute }}'; rejectModalOpen = true"
                                                    class="px-3 py-1.5 text-xs font-medium text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 rounded-xl transition-all">
                                                Reject
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-500">Archived</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No campus gate passes found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($leaves->hasPages())
                <div class="p-4 border-t border-white/[0.06]">
                    {{ $leaves->links() }}
                </div>
            @endif

            <!-- Approve Modal -->
            <div x-show="approveModalOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
                 style="display: none;">
                <div @click.outside="approveModalOpen = false" class="w-full max-w-md rounded-3xl bg-[#121927] border border-white/10 shadow-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <h3 class="text-base font-semibold text-white">Authorize Campus Leave</h3>
                        <button type="button" @click="approveModalOpen = false" class="text-slate-400 hover:text-white transition-colors">&times;</button>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Confirm administrative authorization for <span class="text-white font-semibold" x-text="activeStudentName"></span>. Student will be excused from dorm curfew checks during the approved period.
                    </p>

                    <form :action="activeActionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Administrative Remarks (Optional)</label>
                            <input type="text" name="remarks" placeholder="e.g. Authorized by Chief Administrator."
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-white/[0.06]">
                            <button type="button" @click="approveModalOpen = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                                Confirm Approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reject Modal -->
            <div x-show="rejectModalOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
                 style="display: none;">
                <div @click.outside="rejectModalOpen = false" class="w-full max-w-md rounded-3xl bg-[#121927] border border-white/10 shadow-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <h3 class="text-base font-semibold text-rose-400">Decline Gate Pass</h3>
                        <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-white transition-colors">&times;</button>
                    </div>

                    <form :action="activeActionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Reason for Rejection *</label>
                            <textarea name="rejection_reason" required rows="3" placeholder="Provide justification for decline..."
                                      class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-white/[0.06]">
                            <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-rose-500 hover:bg-rose-400 rounded-xl shadow-lg shadow-rose-500/20 active:scale-[0.98] transition-all">
                                Decline
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>