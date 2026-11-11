<x-app-layout title="Student Feedback & Inquiries">
    <div class="space-y-6" x-data="{
        newCommentOpen: false,
        replyModalOpen: false,
        activeComment: {
            id: null,
            author_name: '',
            author_id: '',
            author_role: '',
            is_student: true,
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
                author_name: c.user ? c.user.name : 'Unknown User',
                author_id: c.user ? c.user.university_id : 'N/A',
                author_role: c.user ? c.user.role : 'student',
                is_student: c.user && c.user.role === 'student',
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
        <!-- Page Header with Post Comment Trigger -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Wing Feedback Desk</span>
                </div>
                <h2 class="text-xl font-bold text-white tracking-tight">Dormitory Block Feedback & Inquiries</h2>
                <p class="text-xs text-slate-400 mt-0.5">Review resident inquiries from your assigned block in a structured table roster and submit field reports.</p>
            </div>
            <button type="button" @click="newCommentOpen = !newCommentOpen" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2 shrink-0">
                <svg class="w-4 h-4 transition-transform duration-200" :class="newCommentOpen ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span x-text="newCommentOpen ? 'Close Form' : '+ Give / Post Comment'">+ Give / Post Comment</span>
            </button>
        </div>

        <!-- Proctor Post Comment / Report Box (Collapsible) -->
        <div x-show="newCommentOpen" x-cloak class="bg-[#121927]/90 backdrop-blur-md border border-white/[0.09] rounded-3xl p-6 shadow-xl shadow-black/30 space-y-4" style="display: none;">
            <div class="border-b border-white/[0.06] pb-3">
                <h3 class="text-base font-semibold text-white">Post Comment, Maintenance Report or Inquiry</h3>
                <p class="text-xs text-slate-400">Submit an official report or comment visible to dormitory administration.</p>
            </div>

            <form method="POST" action="{{ route('proctor.comments.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Submit To Recipient</label>
                        <select name="target_role" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                            <option value="proctor_manager">Proctor Manager (Supervisor)</option>
                            <option value="admin">Institutional Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Feedback Category</label>
                        <select name="category" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                            <option value="maintenance">Maintenance & Repairs</option>
                            <option value="complaint">Block Incident / Complaint</option>
                            <option value="inquiry">Administrative Inquiry</option>
                            <option value="suggestion">Facility Suggestion</option>
                            <option value="general">General Comment</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Subject / Title</label>
                        <input type="text" name="title" required placeholder="e.g. Water pump maintenance on Block 4" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500" />
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Detailed Description</label>
                    <textarea name="message" rows="3" required placeholder="Describe the issue, requested action, or observation in detail..." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-1">
                    <button type="button" @click="newCommentOpen = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                        Submit Comment
                    </button>
                </div>
            </form>
        </div>

        <!-- Filters Form -->
        <div class="bg-[#111726]/70 border border-white/[0.06] rounded-2xl p-3">
            <form method="GET" action="{{ route('proctor.comments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
                <div class="relative sm:col-span-2">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search comment, student name, ID..."
                           class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white placeholder-slate-500 transition-all" />
                </div>

                <select name="scope" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white transition-all">
                    <option value="">All Relevant (Students & Mine)</option>
                    <option value="students" {{ request('scope') === 'students' ? 'selected' : '' }}>Students in My Block</option>
                    <option value="mine" {{ request('scope') === 'mine' ? 'selected' : '' }}>My Submitted Comments</option>
                </select>

                <select name="category" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white transition-all">
                    <option value="">All Categories</option>
                    <option value="maintenance" {{ request('category') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="suggestion" {{ request('category') === 'suggestion' ? 'selected' : '' }}>Suggestion</option>
                    <option value="complaint" {{ request('category') === 'complaint' ? 'selected' : '' }}>Complaint</option>
                    <option value="inquiry" {{ request('category') === 'inquiry' ? 'selected' : '' }}>Inquiry</option>
                    <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>General</option>
                </select>

                <select name="status" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white transition-all">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'category', 'status', 'scope']))
                        <a href="{{ route('proctor.comments.index') }}" class="px-3 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.03] hover:bg-white/[0.06] border border-white/10 rounded-xl transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Structured Table Format for Proctor Comments -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Author</th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Title & Message</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Official Response</th>
                            <th class="px-5 py-3.5">Submitted</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
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
                                        'name' => $cm->user->name ?? 'User',
                                        'university_id' => $cm->user->university_id ?? 'N/A',
                                        'role' => $cm->user->role ?? 'student',
                                    ],
                                    'responder' => $cm->responder ? ['name' => $cm->responder->name] : null
                                ];
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $cm->user->avatar_url ?? '' }}" alt="" class="w-7 h-7 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-semibold text-white block">{{ $cm->user->name ?? 'User' }}</span>
                                                @if($cm->user_id === auth()->id())
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">You</span>
                                                @elseif($cm->user && $cm->user->role === 'student')
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">Student</span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-slate-400 flex items-center gap-1.5">
                                                <span class="text-emerald-400 font-mono">{{ $cm->user->university_id ?? 'N/A' }}</span>
                                                @if($cm->target_role)
                                                    <span>&bull; To: <span class="capitalize text-slate-300">{{ str_replace('_', ' ', $cm->target_role) }}</span></span>
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
                                    <span class="font-semibold text-white block truncate">{{ $cm->title }}</span>
                                    <span class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $cm->message }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium capitalize {{ $cm->status === 'resolved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($cm->status === 'in_progress' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20') }}">
                                        {{ str_replace('_', ' ', $cm->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 max-w-xs">
                                    @if($cm->response)
                                        <div class="text-[11px]">
                                            <span class="text-emerald-400 font-medium block text-[10px]">{{ $cm->responder?->name ?? 'Official Response' }}</span>
                                            <span class="text-slate-300 line-clamp-1">{{ $cm->response }}</span>
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
                                        <button type="button" @click="openReply({{ Js::from($cmData) }})" class="px-3 py-1.5 text-[11px] font-medium rounded-xl border transition-colors {{ $cm->response ? 'text-slate-300 bg-white/[0.05] hover:bg-white/[0.09] border-white/10' : 'text-slate-950 bg-emerald-500 hover:bg-emerald-400 border-transparent shadow-sm' }}">
                                            {{ $cm->user_id === auth()->id() ? 'View Details' : ($cm->response ? 'View / Edit Reply' : 'Reply') }}
                                        </button>

                                        <!-- Delete Icon and Functionality -->
                                        <form method="POST" action="{{ route('proctor.comments.destroy', $cm) }}" onsubmit="return confirm('Permanently delete comment: {{ addslashes($cm->title) }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Comment" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg border border-rose-500/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                                    <p class="font-semibold text-white mb-1">No Comments or Feedback Found</p>
                                    <p class="text-xs text-slate-500">No inquiries or feedback reported for your assigned blocks matching the current filters.</p>
                                    <div class="mt-3">
                                        <button type="button" @click="newCommentOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-500 text-slate-950 hover:bg-emerald-400 shadow-md">
                                            + Submit New Comment
                                        </button>
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

        <!-- ========================================== -->
        <!-- MODAL: View & Reply to Comment             -->
        <!-- ========================================== -->
        <div x-show="replyModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="replyModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/10 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider bg-white/[0.05] text-slate-300 border border-white/10" x-text="activeComment.category"></span>
                            <h3 class="text-base font-semibold text-white tracking-tight mt-1.5" x-text="activeComment.title"></h3>
                            <p class="text-xs text-slate-400" x-text="'By ' + activeComment.author_name + ' (' + activeComment.author_id + ') • ' + activeComment.created_at"></p>
                        </div>
                        <button type="button" @click="replyModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Message Body -->
                    <div class="my-4 p-4 rounded-2xl bg-[#0F1420] border border-white/[0.06] text-xs text-slate-200 leading-relaxed">
                        <span class="text-[10px] font-semibold uppercase text-slate-400 block mb-1">Message Content:</span>
                        <p x-text="activeComment.message" class="whitespace-pre-line"></p>
                    </div>

                    <!-- Existing Response Preview if any -->
                    <div x-show="activeComment.response" class="mb-4 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs">
                        <span class="text-emerald-400 font-semibold block text-[10px]" x-text="'Current Reply (by ' + (activeComment.responder_name || 'Dormitory Staff') + '):'"></span>
                        <p x-text="activeComment.response" class="text-white mt-0.5"></p>
                    </div>

                    <!-- Reply Form for Proctor -->
                    <template x-if="activeComment.id">
                        <form method="POST" :action="'{{ url('/proctor/comments') }}/' + activeComment.id + '/reply'" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Update Status</label>
                                <select name="status" x-model="activeComment.status" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                                    <option value="in_progress">In Progress (Maintenance / Action Dispatched)</option>
                                    <option value="resolved">Resolved (Issue Addressed / Repaired)</option>
                                    <option value="closed">Closed (Notice Provided)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Official Response Note</label>
                                <textarea name="response" rows="3" required placeholder="Write official proctor resolution note..." x-model="activeComment.response"
                                          class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" @click="replyModalOpen = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                    Cancel
                                </button>
                                <button type="submit" class="px-5 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                                    Submit Official Reply
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>