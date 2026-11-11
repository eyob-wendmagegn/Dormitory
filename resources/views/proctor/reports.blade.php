<x-app-layout title="Proctor Reports">
    <div class="space-y-6">
        <!-- Header Banner -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 mb-1 block">Supervisory Analytics</span>
                <h2 class="text-2xl font-bold tracking-tight text-white">Block Utilization & Occupancy Report</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">Capacity and resident distribution across your managed blocks (Standard: 50 rooms &bull; 300 beds per hall).</p>
            </div>
            <button onclick="window.print()" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Report</span>
            </button>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <dashboard-stats title="Assigned Blocks" value="{{ $totalBlocks }}" subtitle="Managed Campus Halls" color="white"></dashboard-stats>
            <dashboard-stats title="Design Rooms" value="{{ $totalBlocks * 50 }}" subtitle="50 Rooms per Block" color="white"></dashboard-stats>
            <dashboard-stats title="Standard Capacity" value="{{ $totalCapacity }}" subtitle="300 Beds per Block" color="white"></dashboard-stats>
            <dashboard-stats title="Active Residents" value="{{ $totalAllocated }}" subtitle="{{ $totalCapacity > 0 ? round(($totalAllocated / $totalCapacity) * 100, 1) : 0 }}% Wing Occupancy" color="emerald"></dashboard-stats>
        </div>

        <!-- Table Container -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-4">Block Code</th>
                            <th class="px-5 py-4">Block Name</th>
                            <th class="px-5 py-4">Gender Designation</th>
                            <th class="px-5 py-4">Design Rooms</th>
                            <th class="px-5 py-4">Standard Capacity</th>
                            <th class="px-5 py-4">Active Residents</th>
                            <th class="px-5 py-4">Available Beds</th>
                            <th class="px-5 py-4">Occupancy Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($blocks as $b)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 font-bold text-white font-mono">{{ $b->block_code }}</td>
                                <td class="px-5 py-4 font-semibold text-white">{{ $b->name }}</td>
                                <td class="px-5 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $b->gender_category === 'female' ? 'bg-pink-500/10 text-pink-400 border border-pink-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' }}">
                                        {{ ucfirst($b->gender_category) }} Block
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-medium text-slate-300">{{ $b->rooms_count }} / 50 Rooms</td>
                                <td class="px-5 py-4 font-medium text-slate-300">300 Beds (50 × 6)</td>
                                <td class="px-5 py-4 font-bold text-white">{{ $b->active_allocations_count }}</td>
                                <td class="px-5 py-4 font-bold text-emerald-400">{{ $b->standard_available_beds }}</td>
                                <td class="px-5 py-4 font-bold text-cyan-400">{{ $b->standard_occupancy_rate }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                    No assigned blocks to generate reports for.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
