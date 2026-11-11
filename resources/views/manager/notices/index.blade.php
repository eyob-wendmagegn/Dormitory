<x-app-layout title="Internal Announcements (Manager to Proctors)">
    <div class="space-y-6" x-data="{ noticeModal: false }">
        <!-- Top Header & Broadcast Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Internal Announcements</span>
                    <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-purple-500/10 text-purple-300 font-mono border border-purple-500/20">
                        Manager to Proctors Only
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Proctor Operations Memos & Notices</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Draft and broadcast operational directives, inventory audit schedules, and curfew notices restricted to proctor staff. Students cannot view these internal notices.
                </p>
            </div>

            <button type="button" @click="noticeModal = true" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                <span>Draft Internal Notice</span>
            </button>
        </div>

        <!-- Notices Feed -->
        <div class="space-y-4">
            @forelse($notices as $nt)
                <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] hover:border-white/15 transition-all shadow-xl shadow-black/25 relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-3">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider {{ $nt->priority === 'critical' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($nt->priority === 'urgent' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-white/[0.05] text-slate-300 border border-white/10') }}">
                                    {{ $nt->priority }} priority
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                                    {{ $nt->block ? 'Target: Block ' . $nt->block->block_code : 'Target: All Proctors' }}
                                </span>
                                <span class="text-[11px] text-slate-400 font-mono">
                                    &bull; Posted {{ $nt->published_at?->diffForHumans() }}
                                </span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-white pt-1">{{ $nt->title }}</h3>
                        </div>

                        <form method="POST" action="{{ route('manager.notices.destroy', $nt) }}" onsubmit="return confirm('Remove this internal announcement?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition-all">
                                Delete
                            </button>
                        </form>
                    </div>

                    @if($nt->summary)
                        <p class="text-xs text-emerald-400 font-medium mb-2.5 bg-[#0F1420] p-3 rounded-2xl border border-white/[0.06]">
                            {{ $nt->summary }}
                        </p>
                    @endif

                    <div class="text-xs text-slate-300 leading-relaxed whitespace-pre-line bg-[#0F1420]/70 p-4 rounded-2xl border border-white/[0.06]">
                        {{ $nt->content }}
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-[11px] text-slate-400">
                        <span class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Author: <strong class="text-white">{{ $nt->author?->name ?? 'Proctor Manager' }}</strong>
                        </span>
                        <span class="text-[10px] uppercase font-semibold text-purple-400">Restricted To Proctors</span>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
                    <div class="w-12 h-12 rounded-2xl bg-white/[0.05] border border-white/10 flex items-center justify-center text-slate-400 mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h4 class="text-base font-semibold text-white mb-1">No Internal Announcements Posted</h4>
                    <p class="text-xs text-slate-400">Draft operational memos and directives for your proctor team using the button above.</p>
                </div>
            @endforelse

            @if($notices->hasPages())
                <div class="p-4 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07]">
                    {{ $notices->links() }}
                </div>
            @endif
        </div>

        <!-- Broadcast Internal Notice Modal -->
        <div x-show="noticeModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             style="display: none;">
            <div @click.outside="noticeModal = false" class="w-full max-w-lg rounded-3xl bg-[#121927] border border-white/10 shadow-2xl p-6">
                <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                    <div>
                        <h3 class="text-base font-semibold text-white">Draft Internal Notice to Proctors</h3>
                        <p class="text-xs text-slate-400">Restricted to proctor staff &bull; Students cannot view</p>
                    </div>
                    <button type="button" @click="noticeModal = false" class="text-slate-400 hover:text-white transition-colors">&times;</button>
                </div>

                <form method="POST" action="{{ route('manager.notices.store') }}" class="space-y-4 mt-5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Notice Headline / Subject</label>
                        <input type="text" name="title" required placeholder="e.g. Mattress and pillow inventory count is scheduled for this afternoon" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Target Audience</label>
                            <select name="block_id" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                                <option value="">All Proctors (University-Wide)</option>
                                @foreach($blocks as $blk)
                                    <option value="{{ $blk->id }}">Proctor of Block {{ $blk->block_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Priority Level</label>
                            <select name="priority" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                                <option value="normal">Normal Priority</option>
                                <option value="urgent" selected>Urgent (Alert Flag)</option>
                                <option value="critical">Critical / Mandatory</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Summary (Short Memo)</label>
                        <input type="text" name="summary" placeholder="e.g. Please be prepared at 2:00 PM with all block handover slips." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Detailed Message Body</label>
                        <textarea name="content" rows="4" required placeholder="Provide complete instructions, timeline, and requirements for the proctor team..." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                        <button type="button" @click="noticeModal = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                            Broadcast to Proctors
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>