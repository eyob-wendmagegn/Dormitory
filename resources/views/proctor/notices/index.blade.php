<x-app-layout title="Dormitory Announcements & Notices">
    <div class="space-y-6" x-data="{ tab: 'internal', postModal: false }">
        <!-- Top Header & Post Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-semibold tracking-wider text-slate-400">Notice & Announcement Workflow</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                        Block {{ $block?->block_code ?? 'All Assigned' }}
                    </span>
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-white">Dormitory Notices & Operational Memos</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">
                    Review internal directives from Proctor Manager (restricted from students) and publish announcements targeted specifically to residents in your block.
                </p>
            </div>

            <button type="button" @click="postModal = true" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                <span>Post Student Notice</span>
            </button>
        </div>

        <!-- Navigation Tabs (Internal vs Student Notices) -->
        <div class="flex items-center gap-2 border-b border-white/[0.06] pb-3">
            <button type="button" @click="tab = 'internal'"
                    :class="tab === 'internal' ? 'bg-white/10 text-white font-medium border border-white/10 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Internal Manager Notices (Proctor Only)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/[0.08] font-mono text-slate-300">{{ $internalNotices->count() }}</span>
            </button>

            <button type="button" @click="tab = 'student'"
                    :class="tab === 'student' ? 'bg-white/10 text-white font-medium border border-white/10 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/[0.04]'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>My Published Student Notices (Block {{ $block?->block_code }})</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/[0.08] font-mono text-slate-300">{{ $studentNotices->count() }}</span>
            </button>
        </div>

        <!-- TAB 1: Internal Manager Notices (Proctor Only) -->
        <div x-show="tab === 'internal'" class="space-y-4">
            @forelse($internalNotices as $in)
                <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] hover:border-emerald-500/30 transition-all shadow-xl shadow-black/20 relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-2">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wider {{ $in->priority === 'critical' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($in->priority === 'urgent' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-white/[0.05] text-slate-400 border border-white/10') }}">
                                    {{ $in->priority }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                    Manager Directive
                                </span>
                                <span class="text-xs text-slate-400 font-mono">
                                    &bull; Received {{ $in->published_at?->diffForHumans() }}
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-white">{{ $in->title }}</h3>
                        </div>

                        <span class="text-xs uppercase font-medium text-purple-400 bg-purple-500/10 px-3 py-1 rounded-full border border-purple-500/20 shrink-0">
                            Proctors Only &bull; Hidden from Students
                        </span>
                    </div>

                    @if($in->summary)
                        <p class="text-xs text-slate-300 font-medium mb-2.5 bg-[#0F1420] p-3 rounded-2xl border border-white/[0.05]">
                            {{ $in->summary }}
                        </p>
                    @endif

                    <div class="text-xs text-slate-200 leading-relaxed whitespace-pre-line bg-[#0F1420]/60 p-4 rounded-2xl border border-white/[0.05]">
                        {{ $in->content }}
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-xs text-slate-400">
                        <span>From: <strong class="text-white font-medium">{{ $in->author?->name ?? 'Proctor Manager' }}</strong></span>
                        <span class="font-mono text-xs">{{ $in->published_at?->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07]">
                    <h4 class="text-sm font-semibold text-white mb-1">No Internal Memos Currently Active</h4>
                    <p class="text-xs text-slate-400">You are caught up with all operational memos from the Proctor Manager.</p>
                </div>
            @endforelse
        </div>

        <!-- TAB 2: Proctor-to-Students Notices -->
        <div x-show="tab === 'student'" class="space-y-4" style="display: none;">
            @forelse($studentNotices as $sn)
                <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] hover:border-cyan-500/30 transition-all shadow-xl shadow-black/20 relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-2">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wider {{ $sn->priority === 'critical' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($sn->priority === 'urgent' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-white/[0.05] text-slate-400 border border-white/10') }}">
                                    {{ $sn->priority }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                                    Target: Block {{ $block?->block_code }} Residents
                                </span>
                                <span class="text-xs text-slate-400 font-mono">
                                    &bull; Published {{ $sn->published_at?->diffForHumans() }}
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-white">{{ $sn->title }}</h3>
                        </div>

                        <form method="POST" action="{{ route('proctor.notices.destroy', $sn) }}" onsubmit="return confirm('Delete this student notice?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-400 hover:text-white px-3 py-1 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition-all">
                                Delete
                            </button>
                        </form>
                    </div>

                    @if($sn->summary)
                        <p class="text-xs text-cyan-300 font-medium mb-2.5 bg-[#0F1420] p-3 rounded-2xl border border-white/[0.05]">
                            {{ $sn->summary }}
                        </p>
                    @endif

                    <div class="text-xs text-slate-200 leading-relaxed whitespace-pre-line bg-[#0F1420]/60 p-4 rounded-2xl border border-white/[0.05]">
                        {{ $sn->content }}
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-xs text-slate-400">
                        <span class="text-emerald-400">Visible on all Block {{ $block?->block_code }} student dashboards</span>
                        <span class="font-mono text-xs">{{ $sn->published_at?->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07]">
                    <h4 class="text-sm font-semibold text-white mb-1">No Student Notices Published Yet</h4>
                    <p class="text-xs text-slate-400">Publish announcements, curfew reminders, and asset return deadlines for your block residents.</p>
                </div>
            @endforelse
        </div>

        <!-- Post Student Notice Modal -->
        <div x-show="postModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
             style="display: none;">
            <div @click.outside="postModal = false" class="w-full max-w-lg rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6">
                <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                    <div>
                        <h3 class="text-base font-bold text-white">Post Student Notice</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Broadcasts to all residents of Block {{ $block?->block_code }}</p>
                    </div>
                    <button type="button" @click="postModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('proctor.notices.store_student') }}" class="space-y-4 mt-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Headline / Title</label>
                        <input type="text" name="title" required placeholder="e.g. The deadline to return mattresses and pillows is Friday at 5:00 PM." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Target Audience</label>
                            <input type="text" readonly value="Block {{ $block?->block_code ?? 'Assigned' }} Residents" class="w-full bg-[#0F1420] border border-white/[0.06] rounded-xl px-3.5 py-2.5 text-xs text-emerald-400 font-semibold" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Priority</label>
                            <select name="priority" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition-all">
                                <option value="normal">Normal</option>
                                <option value="urgent" selected>Urgent (Alert Flag)</option>
                                <option value="critical">Critical / Mandatory</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Summary / Quick Memo</label>
                        <input type="text" name="summary" placeholder="e.g. Please bring items to proctor office ground floor." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Detailed Notice Content</label>
                        <textarea name="content" rows="4" required placeholder="Provide full details, return instructions, inspection criteria, and consequences for missing the deadline..." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/[0.06]">
                        <button type="button" @click="postModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] rounded-xl border border-white/10 transition-all">Cancel</button>
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 text-xs rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                            Publish Notice to Students
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
