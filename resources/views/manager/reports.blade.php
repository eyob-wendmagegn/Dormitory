<x-app-layout title="Manager Reports">
    <div class="space-y-6">
        <!-- Header Console Banner -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Capacity Auditing</span>
                </div>
                <h2 class="text-xl font-bold text-white tracking-tight">Dormitory Block Utilization Report</h2>
                <p class="text-xs text-slate-400 mt-0.5">Summary of capacity and active placements based on institutional standard (50 rooms &bull; 300 beds per hall).</p>
            </div>
            <button onclick="window.print()" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Report</span>
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <dashboard-stats title="Total Blocks" value="{{ $totalBlocks }}" subtitle="Campus Residence Wings" color="white"></dashboard-stats>
            <dashboard-stats title="Design Rooms" value="{{ $totalBlocks * 50 }}" subtitle="50 Rooms per Block" color="cyan"></dashboard-stats>
            <dashboard-stats title="Institutional Beds" value="{{ $totalCapacity }}" subtitle="300 Beds per Block" color="secondary"></dashboard-stats>
            <dashboard-stats title="Active Occupants" value="{{ $totalAllocated }}" subtitle="{{ $totalCapacity > 0 ? round(($totalAllocated / $totalCapacity) * 100, 1) : 0 }}% System Utilization" color="emerald"></dashboard-stats>
        </div>

        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Campus Block Breakdown</h3>
                    <p class="text-xs text-slate-400">Live residential quotas and standard room capacity audit.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Live Data
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Code</th>
                            <th class="px-5 py-3.5">Hall Name</th>
                            <th class="px-5 py-3.5">Designated Category</th>
                            <th class="px-5 py-3.5">Design Rooms</th>
                            <th class="px-5 py-3.5">Standard Capacity</th>
                            <th class="px-5 py-3.5">Occupied</th>
                            <th class="px-5 py-3.5">Available Beds</th>
                            <th class="px-5 py-3.5">Occupancy Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @foreach($blocks as $b)
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
                                <td class="px-5 py-3.5 font-semibold text-white">{{ $b->active_allocations_count }}</td>
                                <td class="px-5 py-3.5 font-semibold text-emerald-400">{{ $b->standard_available_beds }}</td>
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