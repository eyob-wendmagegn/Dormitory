<x-app-layout title="System Reports">
    <div class="space-y-6">
        <!-- Header Console Banner -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Institutional Analytics & Audits</span>
                </div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Institutional Dormitory Reports</h2>
                <p class="text-xs text-slate-400 mt-0.5">Comprehensive audit summaries, gender demographics, and campus hall utilization.</p>
            </div>
            <button onclick="window.print()" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 transition-all active:scale-[0.98] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Official Report</span>
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <dashboard-stats title="Total Students" value="{{ $stats['total_students'] }}" subtitle="Active Enrollment" color="white"></dashboard-stats>
            <dashboard-stats title="Male Students" value="{{ $stats['male_students'] }}" subtitle="Male Quota" color="cyan"></dashboard-stats>
            <dashboard-stats title="Female Students" value="{{ $stats['female_students'] }}" subtitle="Female Quota" color="secondary"></dashboard-stats>
            <dashboard-stats title="Accommodated" value="{{ $stats['allocated_students'] }}" subtitle="Out of {{ $stats['total_capacity'] }} Beds" color="emerald"></dashboard-stats>
        </div>

        <!-- Hall Breakdown Table -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Dormitory Hall Occupancy Audit</h3>
                    <p class="text-xs text-slate-400">Current live student resident load compared to baseline standard capacity.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Live Data
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Block Code</th>
                            <th class="px-5 py-3.5">Hall Name</th>
                            <th class="px-5 py-3.5">Gender Category</th>
                            <th class="px-5 py-3.5">Design Rooms</th>
                            <th class="px-5 py-3.5">Design Capacity</th>
                            <th class="px-5 py-3.5">Active Residents</th>
                            <th class="px-5 py-3.5">Available Beds</th>
                            <th class="px-5 py-3.5">Occupancy Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @foreach($stats['blocks'] as $b)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 font-bold text-emerald-400 font-mono">{{ $b->block_code }}</td>
                                <td class="px-5 py-3.5 font-semibold text-white">{{ $b->name }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider {{ $b->gender_category === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $b->gender_category }} Block
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400">{{ $b->rooms_count }} / 50 Rooms</td>
                                <td class="px-5 py-3.5 font-semibold text-white">300 Beds (50 × 6)</td>
                                <td class="px-5 py-3.5 text-white font-semibold">{{ $b->active_allocations_count }} Students</td>
                                <td class="px-5 py-3.5 text-emerald-400 font-semibold">{{ $b->standard_available_beds }} Beds</td>
                                <td class="px-5 py-3.5 font-bold text-emerald-400">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-white/[0.06] rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min(100, $b->standard_occupancy_rate) }}%"></div>
                                        </div>
                                        <span>{{ $b->standard_occupancy_rate }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
