<x-app-layout title="Campus Gate Pass & Leave Requests">
    <div class="space-y-6" x-data="{ createModalOpen: false, viewPassModal: false, activePassData: null }">
        <!-- Top Banner -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 mb-1 block">Campus Gate Security & Dorm Curfew Passes</span>
                <h2 class="text-2xl font-bold tracking-tight text-white">Gate Pass & Leave Permissions</h2>
                <p class="text-sm text-slate-400 mt-1 max-w-xl font-normal leading-relaxed">
                    Request permission in advance if leaving campus after curfew or staying overnight elsewhere. Approved passes automatically excuse you from nightly dorm room checks.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        @click="createModalOpen = true"
                        class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Request New Pass</span>
                </button>
            </div>
        </div>

        <!-- Active Approved Gate Pass Digital Card (if active today) -->
        @if($activePass)
            <div class="p-6 rounded-3xl bg-emerald-500/10 border border-emerald-500/20 shadow-xl shadow-black/25 relative overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-emerald-500 text-slate-950">
                                Active Gate Pass
                            </span>
                            <span class="text-xs font-mono font-medium text-emerald-400">
                                #LR-{{ $activePass->id }}
                            </span>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-white">
                            {{ $activePass->type_label }} &bull; Authorized by Proctor
                        </h3>
                        <div class="text-xs text-slate-300 flex flex-wrap items-center gap-3">
                            <span>Valid: <strong class="text-white">{{ $activePass->start_date->format('M d, Y') }}</strong> to <strong class="text-white">{{ $activePass->end_date->format('M d, Y') }}</strong></span>
                            <span>&bull;</span>
                            <span>Destination: <strong class="text-white">{{ $activePass->destination }}</strong></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button"
                                @click="viewPassModal = true"
                                class="bg-white hover:bg-slate-100 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-xs sm:text-sm shadow-md transition-all active:scale-[0.98] flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Show Digital Gate Pass</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Leave Requests Table -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Leave Applications & History</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Complete record of your past and pending gate passes.</p>
                </div>
                <span class="text-xs font-mono text-slate-400 bg-white/[0.05] px-3 py-1 rounded-full border border-white/10">
                    {{ $leaves->count() }} Submissions
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Ref #</th>
                            <th class="px-5 py-4">Type</th>
                            <th class="px-5 py-4">Dates</th>
                            <th class="px-5 py-4">Destination & Reason</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Reviewed By</th>
                            <th class="px-5 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($leaves as $leave)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 font-mono font-medium text-white">
                                    #LR-{{ $leave->id }}
                                </td>
                                <td class="px-5 py-4 font-semibold text-white">
                                    {{ $leave->type_label }}
                                </td>
                                <td class="px-5 py-4 font-mono text-xs">
                                    <div class="text-white font-medium">
                                        {{ $leave->start_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        until {{ $leave->end_date->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-white">{{ $leave->destination }}</div>
                                    <div class="text-xs text-slate-400 truncate max-w-xs mt-0.5" title="{{ $leave->reason }}">
                                        {{ $leave->reason }}
                                    </div>
                                    @if($leave->rejection_reason)
                                        <div class="text-xs text-rose-400 mt-1 font-medium">
                                            Declined Reason: {{ $leave->rejection_reason }}
                                        </div>
                                    @endif
                                    @if($leave->remarks)
                                        <div class="text-xs text-emerald-400 mt-1 font-medium">
                                            Proctor Note: {{ $leave->remarks }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($leave->status === 'approved')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Approved
                                        </span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20 inline-flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Declined
                                        </span>
                                    @elseif($leave->status === 'cancelled')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/[0.05] text-slate-400 border border-white/10">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 inline-flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                            Awaiting Review
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-300">
                                    @if($leave->approver)
                                        <span class="font-medium text-white">{{ $leave->approver->name }}</span>
                                        <span class="block text-[10px] text-slate-400 font-mono">{{ $leave->approved_at?->format('H:i, M d') }}</span>
                                    @else
                                        <span class="text-slate-500">Pending</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($leave->isPending())
                                        <form method="POST" action="{{ route('student.leaves.cancel', $leave) }}" onsubmit="return confirm('Cancel this pending request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 text-xs text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 rounded-xl transition-all">
                                                Cancel
                                            </button>
                                        </form>
                                    @elseif($leave->isApproved())
                                        <button type="button"
                                                onclick="window.print()"
                                                class="px-3 py-1.5 text-xs font-semibold text-slate-950 bg-white hover:bg-slate-100 rounded-xl shadow-md transition-all active:scale-[0.98]">
                                            Print Pass
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-xs text-slate-400">
                                    You have not submitted any gate pass or leave requests yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Request Gate Pass Modal -->
        <div x-show="createModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
             style="display: none;">
            <div @click.outside="createModalOpen = false" class="w-full max-w-lg rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Apply for Gate Pass / Leave</h3>
                    </div>
                    <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('student.leaves.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Pass Category *</label>
                        <select name="request_type" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition-all">
                            <option value="gate_pass">Gate Pass (Single Day Campus Exit)</option>
                            <option value="overnight">Overnight Leave (1 Night)</option>
                            <option value="weekend">Weekend Pass (Friday - Sunday)</option>
                            <option value="medical">Medical / Clinic Leave</option>
                            <option value="emergency">Family / Emergency Leave</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Departure Date *</label>
                            <input type="date" name="start_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Expected Return Date *</label>
                            <input type="date" name="end_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Destination Address / Town *</label>
                            <input type="text" name="destination" required placeholder="e.g. Woldia Town Center / Home (Dessie)"
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Emergency Contact Phone</label>
                            <input type="text" name="emergency_phone" placeholder="e.g. +251 912 345 678"
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Purpose / Detailed Reason *</label>
                        <textarea name="reason" required rows="3" placeholder="Provide accurate justification for campus departure or curfew absence..."
                                  class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                        <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] rounded-xl border border-white/10 transition-all">Cancel</button>
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 text-xs rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Digital Gate Pass Modal -->
        @if($activePass)
            <div x-show="viewPassModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
                 style="display: none;">
                <div @click.outside="viewPassModal = false" class="w-full max-w-md rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6 space-y-4 text-white relative">
                    <!-- University Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/logo.jpg') }}" alt="Woldia University" class="w-10 h-10 object-contain rounded-lg border border-white/10 p-0.5 bg-white/5">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-white">Woldia University</h4>
                                <span class="text-[10px] text-emerald-400 uppercase font-semibold tracking-wider block">Official Campus Gate Pass</span>
                            </div>
                        </div>
                        <button type="button" @click="viewPassModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                    </div>

                    <!-- Pass Verification Tag -->
                    <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-between">
                        <div>
                            <span class="text-[9px] uppercase font-semibold text-emerald-400 block">Authorization Status</span>
                            <span class="text-sm font-bold text-white">OFFICIALLY AUTHORIZED</span>
                        </div>
                        <span class="font-mono text-xs font-bold text-emerald-400">
                            #LR-{{ $activePass->id }}
                        </span>
                    </div>

                    <!-- Student Details -->
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-white/[0.04]">
                            <span class="text-slate-400">Student Resident:</span>
                            <span class="font-semibold text-white">{{ $student->name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-white/[0.04]">
                            <span class="text-slate-400">University ID:</span>
                            <span class="font-mono font-medium text-emerald-400">{{ $student->university_id }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-white/[0.04]">
                            <span class="text-slate-400">Assigned Room:</span>
                            <span class="font-medium text-white">{{ $allocation?->room->block->name }} - Rm {{ $allocation?->room->room_number }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-white/[0.04]">
                            <span class="text-slate-400">Pass Classification:</span>
                            <span class="font-semibold text-amber-300">{{ $activePass->type_label }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-white/[0.04]">
                            <span class="text-slate-400">Valid Dates:</span>
                            <span class="font-medium text-white font-mono">{{ $activePass->start_date->format('M d, Y') }} to {{ $activePass->end_date->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-white/[0.04]">
                            <span class="text-slate-400">Destination:</span>
                            <span class="font-medium text-white">{{ $activePass->destination }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-white/[0.04]">
                            <span class="text-slate-400">Approved By:</span>
                            <span class="font-medium text-white">{{ $activePass->approver?->name ?? 'Block Proctor' }}</span>
                        </div>
                    </div>

                    <!-- Barcode Stamp Mockup -->
                    <div class="pt-2 text-center">
                        <div class="inline-block p-2.5 rounded-xl bg-white/[0.03] border border-white/[0.06] font-mono text-xs tracking-widest text-slate-400">
                            ||| | ||||| || |||||| | ||| || |||||
                        </div>
                        <span class="block text-[10px] text-slate-500 mt-1">Show this digital pass to Campus Security when exiting or re-entering gates.</span>
                    </div>

                    <div class="pt-2">
                        <button type="button" onclick="window.print()" class="w-full py-2.5 text-xs font-semibold text-slate-950 bg-white hover:bg-slate-100 rounded-xl transition-all shadow-md">
                            Print Gate Pass
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
