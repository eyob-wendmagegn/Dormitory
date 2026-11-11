<x-app-layout title="Student Feedback & Inquiries">
    <div class="space-y-6" x-data="{
        replyModalOpen: false,
        activeComment: {
            id: null,
            student_name: '',
            university_id: '',
            category: '',
            title: '',
            message: '',
            status: 'resolved',
            response: '',
            responder_name: '',
            created_at: ''
        },
        openReply(c) {
            this.activeComment = {
                id: c.id,
                student_name: c.user ? c.user.name : 'Unknown Student',
                university_id: c.user ? c.user.university_id : 'N/A',
                category: c.category,
                title: c.title,
                message: c.message,
                status: c.status || 'resolved',
                response: c.response || '',
                responder_name: c.responder ? c.responder.name : '',
                created_at: c.created_at_formatted || ''
            };
            this.replyModalOpen = true;
        }
    }">
        <!-- Page Header -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90 mb-1 block">Feedback Dispatch</span>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Accommodation Feedback & Staff Reports</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">Review, track, and provide official responses to reports submitted by proctors, proctor managers, and student residents in a structured table roster.</p>
        </div>

        <!-- Filters Form -->
        <div class="p-4 rounded-2xl bg-[#111726]/70 border border-white/[0.06] backdrop-blur-md">
            <form method="GET" action="{{ route('admin.comments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
                <div class="relative sm:col-span-2">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search comment, subject, author name or ID..."
                           class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl pl-9 pr-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                </div>

                <select name="author_role" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">Proctors & Managers (Default)</option>
                    <option value="all" {{ request('author_role') === 'all' ? 'selected' : '' }}>All Inquiries (Inc. Students)</option>
                    <option value="proctor" {{ request('author_role') === 'proctor' ? 'selected' : '' }}>Proctors Only</option>
                    <option value="proctor_manager" {{ request('author_role') === 'proctor_manager' ? 'selected' : '' }}>Proctor Managers Only</option>
                    <option value="student" {{ request('author_role') === 'student' ? 'selected' : '' }}>Students Only</option>
                </select>

                <select name="category" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Categories</option>
                    <option value="maintenance" {{ request('category') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="suggestion" {{ request('category') === 'suggestion' ? 'selected' : '' }}>Suggestion</option>
                    <option value="complaint" {{ request('category') === 'complaint' ? 'selected' : '' }}>Complaint</option>
                    <option value="inquiry" {{ request('category') === 'inquiry' ? 'selected' : '' }}>Inquiry</option>
                    <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>General</option>
                </select>

                <select name="status" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-md shadow-emerald-500/20 active:scale-[0.98]">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'author_role', 'category', 'status']))
                        <a href="{{ route('admin.comments.index') }}" class="px-3.5 py-2.5 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] border border-white/10 rounded-xl transition-all flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Structured Table Format for Comments -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.03] text-slate-400 uppercase text-[10px] font-semibold border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Student / Resident</th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Title & Message</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Official Response</th>
                            <th class="px-5 py-3.5">Submitted</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($comments as $cm)
                            @php
                                $cmData = [
                                    'id' => $cm->id,
                                    'category' => $cm->category,
                                    'title' => $cm->title,
                                    'message' => $cm->message,
                                    'status' => $cm->status,
                                    'response' => $cm->response,
                                    'created_at_formatted' => $cm->created_at->format('M d, Y h:i A'),
                                    'user' => [
                                        'name' => $cm->user->name ?? 'Student',
                                        'university_id' => $cm->user->university_id ?? 'N/A',
                                    ],
                                    'responder' => $cm->responder ? ['name' => $cm->responder->name] : null
                                ];
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $cm->user->avatar_url ?? '' }}" alt="" class="w-7 h-7 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-medium text-white block">{{ $cm->user->name ?? 'User' }}</span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-medium uppercase {{ $cm->user?->role === 'proctor_manager' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : ($cm->user?->role === 'proctor' ? 'bg-violet-500/10 text-violet-400 border border-violet-500/20' : 'bg-white/[0.05] text-slate-300 border border-white/10') }}">
                                                    {{ str_replace('_', ' ', $cm->user?->role ?? 'user') }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                                <span class="text-emerald-400 font-mono">{{ $cm->user->university_id ?? 'N/A' }}</span>
                                                @if($cm->target_role)
                                                    <span>&bull; To: <span class="capitalize text-white">{{ str_replace('_', ' ', $cm->target_role) }}</span></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider bg-white/[0.05] text-slate-300 border border-white/10">
                                        {{ $cm->category }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 max-w-xs">
                                    <span class="font-medium text-white block truncate">{{ $cm->title }}</span>
                                    <span class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $cm->message }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium capitalize {{ $cm->status === 'resolved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($cm->status === 'in_progress' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20') }}">
                                        {{ str_replace('_', ' ', $cm->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 max-w-xs">
                                    @if($cm->response)
                                        <div class="text-[11px]">
                                            <span class="text-emerald-400 font-medium block text-[10px]">{{ $cm->responder?->name ?? 'Official' }}</span>
                                            <span class="text-slate-200 line-clamp-1">{{ $cm->response }}</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-amber-400 italic bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/20">
                                            Awaiting Reply
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-[11px] text-slate-400">
                                    {{ $cm->created_at->diffForHumans() }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="openReply({{ Js::from($cmData) }})" class="px-3 py-1.5 text-xs font-semibold rounded-xl border transition-all active:scale-[0.98] {{ $cm->response ? 'text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border-white/10' : 'text-slate-950 bg-emerald-500 hover:bg-emerald-400 border-emerald-500 shadow-sm shadow-emerald-500/20' }}">
                                            {{ $cm->response ? 'View / Edit' : 'Reply' }}
                                        </button>

                                        <form method="POST" action="{{ route('admin.comments.destroy', $cm) }}" onsubmit="return confirm('Permanently delete comment #{{ $cm->id }} ({{ addslashes($cm->title) }})?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Comment" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg border border-transparent hover:border-rose-500/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    No student comments or feedback reports found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-white/[0.06]">
                {{ $comments->links() }}
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: View & Reply to Comment             -->
        <!-- ========================================== -->
        <div x-show="replyModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="replyModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/[0.08] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 sm:p-7">
                    <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase bg-white/[0.05] text-slate-300 border border-white/10" x-text="activeComment.category"></span>
                            <h3 class="text-base font-semibold text-white tracking-tight mt-1" x-text="activeComment.title"></h3>
                            <p class="text-xs text-slate-400" x-text="'By ' + activeComment.student_name + ' (' + activeComment.university_id + ') • ' + activeComment.created_at"></p>
                        </div>
                        <button type="button" @click="replyModalOpen = false" class="text-slate-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Message Body -->
                    <div class="my-4 p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05] text-xs text-slate-200 leading-relaxed">
                        <span class="text-[10px] font-semibold uppercase text-slate-400 block mb-1">Student Message:</span>
                        <p x-text="activeComment.message" class="whitespace-pre-line"></p>
                    </div>

                    <!-- Existing Response Preview if any -->
                    <div x-show="activeComment.response" class="mb-4 p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs">
                        <span class="text-emerald-400 font-semibold block text-[10px]" x-text="'Current Reply (by ' + (activeComment.responder_name || 'Staff') + '):'"></span>
                        <p x-text="activeComment.response" class="text-white mt-0.5"></p>
                    </div>

                    <!-- Response Form -->
                    <form :action="'/admin/comments/' + activeComment.id + '/reply'" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Official Administrative Reply</label>
                            <textarea name="response" rows="3" required x-model="activeComment.response" placeholder="Type official administrative reply to the student..." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl p-3 text-xs text-white placeholder-slate-500 transition-all"></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Update Ticket Status</label>
                            <select name="status" x-model="activeComment.status" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                <option value="resolved">Mark as Resolved</option>
                                <option value="in_progress">Mark as In Progress</option>
                                <option value="closed">Close Ticket</option>
                            </select>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-white/[0.06]">
                            <button type="button" @click="replyModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                Close
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98]">
                                Submit Official Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
