<x-app-layout title="Leave Requests & Gate Passes">
    <div class="space-y-6">
        <!-- Top Banner -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 mb-1 block">Resident Safety & Curfew Gate Passes</span>
                <h2 class="text-2xl font-bold tracking-tight text-white">Leave Requests & Gate Passes</h2>
                <p class="text-sm text-slate-400 mt-1 max-w-xl font-normal leading-relaxed">
                    Review and authorize campus departures or overnight stays for residents in your assigned block(s). Approved requests are automatically integrated into nightly curfew rounds.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('proctor.attendance.index') }}" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Curfew Rounds</span>
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards & Status Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('proctor.leaves.index', ['status' => 'pending']) }}"
               class="p-5 rounded-2xl border transition-all {{ $status === 'pending' ? 'bg-[#172134]/90 border-emerald-500/40 shadow-lg' : 'bg-[#172134]/60 border-white/[0.05] hover:border-white/10' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider">Awaiting Decision</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 {{ $counts['pending'] > 0 ? 'animate-ping' : '' }}"></span>
                </div>
                <div class="text-3xl font-bold font-mono text-white mt-1">{{ $counts['pending'] }}</div>
                <div class="text-xs text-slate-400 mt-0.5">Pending Gate Passes</div>
            </a>

            <a href="{{ route('proctor.leaves.index', ['status' => 'approved']) }}"
               class="p-5 rounded-2xl border transition-all {{ $status === 'approved' ? 'bg-[#172134]/90 border-emerald-500/40 shadow-lg' : 'bg-[#172134]/60 border-white/[0.05] hover:border-white/10' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Authorized</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                </div>
                <div class="text-3xl font-bold font-mono text-white mt-1">{{ $counts['approved'] }}</div>
                <div class="text-xs text-slate-400 mt-0.5">Approved Active / Past</div>
            </a>

            <a href="{{ route('proctor.leaves.index', ['status' => 'rejected']) }}"
               class="p-5 rounded-2xl border transition-all {{ $status === 'rejected' ? 'bg-[#172134]/90 border-rose-500/40 shadow-lg' : 'bg-[#172134]/60 border-white/[0.05] hover:border-white/10' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-rose-400 uppercase tracking-wider">Declined</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                </div>
                <div class="text-3xl font-bold font-mono text-white mt-1">{{ $counts['rejected'] }}</div>
                <div class="text-xs text-slate-400 mt-0.5">Rejected Submissions</div>
            </a>
        </div>

        <!-- Leave Requests Table / Cards -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20"
             x-data="{
                 approveModalOpen: false,
                 rejectModalOpen: false,
                 activeLeaveId: null,
                 activeStudentName: '',
                 activeActionUrl: ''
             }">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base font-bold text-white tracking-tight">Requests Roster</span>
                    <span class="text-xs text-slate-400 font-mono">({{ $leaves->total() }} Records)</span>
                </div>

                <!-- Status Filter Pills -->
                <div class="flex items-center gap-1.5 text-xs font-medium">
                    <a href="{{ route('proctor.leaves.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $status === 'all' ? 'bg-white/10 text-white border border-white/10' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' }}">All</a>
                    <a href="{{ route('proctor.leaves.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $status === 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' }}">Pending</a>
                    <a href="{{ route('proctor.leaves.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' }}">Approved</a>
                    <a href="{{ route('proctor.leaves.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $status === 'rejected' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' }}">Rejected</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Ref #</th>
                            <th class="px-5 py-4">Student Resident</th>
                            <th class="px-5 py-4">Dorm Location</th>
                            <th class="px-5 py-4">Pass Type</th>
                            <th class="px-5 py-4">Period & Destination</th>
                            <th class="px-5 py-4">Emergency Contact</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($leaves as $leave)
                            @php
                                $student = $leave->student;
                                $alloc = $student?->activeAllocation;
                                $room = $alloc?->room;
                                $block = $room?->block;
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 font-mono font-medium text-white">
                                    #LR-{{ $leave->id }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}" class="w-8 h-8 rounded-lg object-cover border border-white/10">
                                        <div>
                                            <span class="block font-semibold text-white text-xs">{{ $student->name }}</span>
                                            <span class="block text-[10px] text-slate-400 font-mono">{{ $student->university_id }} &bull; {{ $student->department ?? 'General' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($room)
                                        <span class="font-medium text-white">{{ $block?->block_code }}</span> - Rm {{ $room->room_number }}
                                    @else
                                        <span class="text-slate-500">Unallocated</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/[0.05] text-white border border-white/10">
                                        {{ $leave->type_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-white">
                                        {{ $leave->start_date->format('M d') }} &rarr; {{ $leave->end_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Dest: <span class="text-white font-medium">{{ $leave->destination }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 italic truncate max-w-xs mt-0.5" title="{{ $leave->reason }}">
                                        "{{ $leave->reason }}"
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-slate-300 text-xs">
                                    {{ $leave->emergency_phone ?: 'None Provided' }}
                                </td>
                                <td class="px-5 py-4">
                                    @if($leave->status === 'approved')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Approved
                                        </span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            Declined
                                        </span>
                                    @elseif($leave->status === 'cancelled')
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/[0.05] text-slate-400 border border-white/10">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Pending Review
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($leave->isPending())
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                    @click="activeLeaveId = {{ $leave->id }}; activeStudentName = '{{ addslashes($student->name) }}'; activeActionUrl = '{{ route('proctor.leaves.approve', $leave) }}'; approveModalOpen = true"
                                                    class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-3 py-1.5 text-xs rounded-xl shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all">
                                                Approve
                                            </button>
                                            <button type="button"
                                                    @click="activeLeaveId = {{ $leave->id }}; activeStudentName = '{{ addslashes($student->name) }}'; activeActionUrl = '{{ route('proctor.leaves.reject', $leave) }}'; rejectModalOpen = true"
                                                    class="px-3 py-1.5 text-xs font-medium text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 rounded-xl transition-all">
                                                Reject
                                            </button>
                                        </div>
                                    @else
                                        <div class="text-xs text-slate-500">
                                            Reviewed by {{ $leave->approver?->name ?? 'Proctor' }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-xs text-slate-400">
                                    No leave requests found for this filter.
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
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
                 style="display: none;">
                <div @click.outside="approveModalOpen = false" class="w-full max-w-md rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <h3 class="text-base font-bold text-white">Approve Gate Pass</h3>
                        <button type="button" @click="approveModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Authorize leave request for <span class="text-white font-semibold" x-text="activeStudentName"></span>. They will be automatically designated as <strong class="text-amber-400">"On Leave"</strong> during curfew attendance rounds for the specified dates.
                    </p>

                    <form :action="activeActionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Approval Notes / Instructions (Optional)</label>
                            <input type="text" name="remarks" placeholder="e.g. Approved. Return strictly before 21:00 on scheduled date."
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                            <button type="button" @click="approveModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] rounded-xl border border-white/10 transition-all">Cancel</button>
                            <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 text-xs rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
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
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
                 style="display: none;">
                <div @click.outside="rejectModalOpen = false" class="w-full max-w-md rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <h3 class="text-base font-bold text-rose-400">Decline Gate Pass</h3>
                        <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Reject leave request for <span class="text-white font-semibold" x-text="activeStudentName"></span>. A notification with the reason will be sent to the student.
                    </p>

                    <form :action="activeActionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Reason for Rejection *</label>
                            <textarea name="rejection_reason" required rows="3" placeholder="e.g. Upcoming exams week; campus departures restricted without department head endorsement."
                                      class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                            <button type="button" @click="rejectModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] rounded-xl border border-white/10 transition-all">Cancel</button>
                            <button type="submit" class="bg-rose-500 hover:bg-rose-400 text-white font-semibold px-5 py-2.5 text-xs rounded-xl shadow-lg shadow-rose-500/20 active:scale-[0.98] transition-all">
                                Decline Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
