<x-app-layout title="Room Allocations & History">
    <div class="space-y-6">
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90 mb-1 block">Placement Audit</span>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Active & Historical Student Allocations</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">Permanent institutional logs of student room placements across campus halls.</p>
        </div>

        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.03] text-slate-400 uppercase text-[10px] font-semibold border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Student</th>
                            <th class="px-5 py-3.5">Gender</th>
                            <th class="px-5 py-3.5">Assigned Hall & Room</th>
                            <th class="px-5 py-3.5">Allocated On</th>
                            <th class="px-5 py-3.5">Allocated By</th>
                            <th class="px-5 py-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($allocations as $alloc)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 font-medium text-white">{{ $alloc->student->name }} ({{ $alloc->student->university_id }})</td>
                                <td class="px-5 py-3.5 capitalize text-slate-300">{{ $alloc->student->gender ?? 'N/A' }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="text-emerald-400 font-semibold font-mono">{{ $alloc->room->block->block_code }} &bull; Room {{ $alloc->room->room_number }}</span>
                                    <span class="text-[10px] text-slate-400 block">Block: {{ $alloc->room->block->name }} ({{ $alloc->room->block->gender_category }})</span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400">{{ $alloc->allocated_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3.5 text-slate-200">{{ $alloc->allocator?->name ?? 'System Admin' }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium capitalize {{ $alloc->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-white/[0.05] text-slate-400 border border-white/10' }}">
                                        {{ $alloc->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    No room allocations recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-white/[0.06]">
                {{ $allocations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
