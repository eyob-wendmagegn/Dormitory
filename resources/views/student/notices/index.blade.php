<x-app-layout title="Dormitory Notices & Announcements">
    <div class="space-y-6">
        <!-- Top Header Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-semibold tracking-wider text-slate-400">Notice & Announcement Workflow</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                        {{ $allocation ? 'Block ' . $allocation->room?->block?->block_code . ' Resident' : 'Campus Resident' }}
                    </span>
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-white">Dormitory Announcements & Bulletins</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">
                    Official notices, curfew reminders, and asset return schedules published by your Block Proctor and Dormitory Administration.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('student.assets.index') }}" class="bg-white/[0.05] hover:bg-white/[0.09] text-slate-300 hover:text-white border border-white/10 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>My Assets & Clearance</span>
                </a>
            </div>
        </div>

        <!-- Notices Feed -->
        <div class="space-y-4">
            @forelse($notices as $nt)
                <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border {{ $nt->priority === 'critical' ? 'border-rose-500/30' : ($nt->priority === 'urgent' ? 'border-amber-500/30' : 'border-white/[0.07]') }} hover:border-emerald-500/30 transition-all shadow-xl shadow-black/20 relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-3">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wider {{ $nt->priority === 'critical' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($nt->priority === 'urgent' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-white/[0.05] text-slate-400 border border-white/10') }}">
                                    {{ $nt->priority }} priority
                                </span>
                                @if($nt->block)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                                        Block {{ $nt->block->block_code }} Notice
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 font-mono">
                                        Campus-Wide Announcement
                                    </span>
                                @endif
                                <span class="text-xs text-slate-400 font-mono">
                                    &bull; Published {{ $nt->published_at?->diffForHumans() }}
                                </span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-white pt-1">{{ $nt->title }}</h3>
                        </div>
                    </div>

                    @if($nt->summary)
                        <p class="text-xs text-cyan-300 font-medium mb-3 bg-[#0F1420] p-3 rounded-2xl border border-white/[0.05]">
                            {{ $nt->summary }}
                        </p>
                    @endif

                    <div class="text-xs text-slate-200 leading-relaxed whitespace-pre-line bg-[#0F1420]/60 p-4 rounded-2xl border border-white/[0.05]">
                        {{ $nt->content }}
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-xs text-slate-400">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Author: <strong class="text-white font-medium">{{ $nt->author?->name ?? 'Block Proctor' }}</strong>
                            <span class="text-slate-500">({{ ucfirst(str_replace('_', ' ', $nt->author?->role ?? 'Administration')) }})</span>
                        </span>
                        <span class="font-mono text-xs">{{ $nt->published_at?->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07]">
                    <div class="w-12 h-12 rounded-2xl bg-white/[0.04] border border-white/10 flex items-center justify-center text-slate-400 mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <h4 class="text-sm font-semibold text-white mb-1">No Active Notices</h4>
                    <p class="text-xs text-slate-400">No bulletins have been posted for your block at this time.</p>
                </div>
            @endforelse

            @if($notices->hasPages())
                <div class="p-4 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07]">
                    {{ $notices->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
