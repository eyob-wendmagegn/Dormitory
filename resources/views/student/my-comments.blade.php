<x-app-layout title="My Feedback & Requests">
    <div class="space-y-6" x-data="{
        detailModalOpen: false,
        activeComment: {
            title: '',
            category: '',
            message: '',
            status: '',
            response: '',
            responder_name: '',
            created_at: ''
        },
        openDetail(c) {
            this.activeComment = {
                title: c.title,
                category: c.category,
                message: c.message,
                status: c.status,
                response: c.response || '',
                responder_name: c.responder ? c.responder.name : 'Dormitory Official',
                created_at: c.created_at_formatted
            };
            this.detailModalOpen = true;
        }
    }">
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 mb-1 block">Help Desk Records</span>
                <h2 class="text-2xl font-bold tracking-tight text-white">My Dormitory Requests & Inquiries</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">Track the status and official proctor responses to your submitted maintenance and living reports in table format.</p>
            </div>
            <a href="{{ route('student.comments.create') }}" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Request</span>
            </a>
        </div>

        <!-- Table Format for Student Inquiries -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4">Subject & Message</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Official Response</th>
                            <th class="px-5 py-4">Submitted</th>
                            <th class="px-5 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($comments as $cm)
                            @php
                                $cmData = [
                                    'title' => $cm->title,
                                    'category' => $cm->category,
                                    'message' => $cm->message,
                                    'status' => $cm->status,
                                    'response' => $cm->response,
                                    'created_at_formatted' => $cm->created_at->format('M d, Y - h:i A'),
                                    'responder' => $cm->responder ? ['name' => $cm->responder->name] : null
                                ];
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium uppercase tracking-wider bg-white/[0.05] text-white border border-white/10">
                                        {{ $cm->category }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 max-w-sm">
                                    <span class="font-semibold text-white block">{{ $cm->title }}</span>
                                    <span class="text-xs text-slate-400 line-clamp-2 mt-0.5">{{ $cm->message }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium capitalize {{ $cm->status === 'resolved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($cm->status === 'in_progress' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20') }}">
                                        {{ str_replace('_', ' ', $cm->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    @if($cm->response)
                                        <div class="text-xs">
                                            <span class="text-emerald-400 font-semibold block text-[10px]">
                                                {{ $cm->responder?->name ?? 'Dormitory Official' }}
                                            </span>
                                            <p class="text-slate-200 line-clamp-2 mt-0.5 leading-relaxed">{{ $cm->response }}</p>
                                        </div>
                                    @else
                                        <span class="text-xs text-amber-400 italic bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/20">
                                            Under Review
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-400">
                                    {{ $cm->created_at->diffForHumans() }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" @click="openDetail({{ Js::from($cmData) }})" class="px-3 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <p class="font-semibold text-white text-sm">No Inquiries Submitted</p>
                                        <p class="text-xs text-slate-400">You have not submitted any room maintenance or housing inquiries yet.</p>
                                        <div class="pt-2">
                                            <a href="{{ route('student.comments.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all shadow-sm">
                                                Submit First Inquiry
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($comments->hasPages())
                <div class="p-4 border-t border-white/[0.06]">
                    {{ $comments->links() }}
                </div>
            @endif
        </div>

        <!-- Detail Modal -->
        <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-md" @click="detailModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase bg-white/[0.06] text-white border border-white/10" x-text="activeComment.category"></span>
                            <h3 class="text-base font-bold text-white tracking-tight mt-1.5" x-text="activeComment.title"></h3>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="'Submitted: ' + activeComment.created_at"></p>
                        </div>
                        <button type="button" @click="detailModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Message Body -->
                    <div class="p-4 rounded-2xl bg-[#0F1420] border border-white/[0.06] text-xs text-slate-200 leading-relaxed">
                        <span class="text-[10px] font-semibold uppercase text-slate-400 block mb-1">Your Inquiry:</span>
                        <p x-text="activeComment.message" class="whitespace-pre-line"></p>
                    </div>

                    <!-- Official Response -->
                    <div x-show="activeComment.response" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs">
                        <span class="text-emerald-400 font-semibold block text-[10px]" x-text="'Official Response (' + (activeComment.responder_name || 'Dormitory Administration') + '):'"></span>
                        <p x-text="activeComment.response" class="text-slate-200 mt-1 whitespace-pre-line leading-relaxed"></p>
                    </div>

                    <div x-show="!activeComment.response" class="p-3.5 rounded-2xl bg-[#0F1420] border border-dashed border-white/10 text-xs text-amber-400 italic">
                        Your inquiry is currently under active review by the dormitory proctor staff.
                    </div>

                    <div class="flex justify-end pt-3">
                        <button type="button" @click="detailModalOpen = false" class="px-4 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
