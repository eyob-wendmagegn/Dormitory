<x-app-layout title="Warehouse Assets & Inventory Transfer">
    <div class="space-y-6" x-data="{ transferModal: false, stockModal: false }">
        <!-- Top Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Asset / Inventory Workflow (Stage 1)</span>
                    <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                        Manager to Proctor Handover
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Warehouse Assets & Inventory Control</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Count warehouse mattresses and pillows, allocate stocks to block proctors, and monitor dedicated proctor balances.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" @click="transferModal = true" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Transfer Assets</span>
                </button>
                <button type="button" @click="stockModal = true" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Receive Shipment / Restock</span>
                </button>
            </div>
        </div>

        <!-- Warehouse Inventory Stat Cards (Mattress & Pillow) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Mattress Card -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 relative overflow-hidden group">
                <div class="flex items-start justify-between relative z-10 mb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-lg shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Warehouse Inventory</span>
                            <h3 class="text-lg font-bold text-white">{{ $mattressInventory->item_name }}</h3>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-medium px-3 py-1 rounded-full bg-white/[0.05] text-emerald-400 border border-white/10">
                        Penalty: ETB {{ number_format($mattressInventory->unit_penalty_charge, 2) }}
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center mb-4 relative z-10">
                    <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block tracking-wider">Available in Wh</span>
                        <span class="text-2xl font-bold text-white font-mono block mt-1">{{ number_format($mattressInventory->available_quantity) }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block tracking-wider">Allocated to Proctors</span>
                        <span class="text-2xl font-bold text-emerald-400 font-mono block mt-1">{{ number_format($mattressInventory->allocated_quantity) }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block tracking-wider">Total Stock</span>
                        <span class="text-2xl font-bold text-cyan-400 font-mono block mt-1">{{ number_format($mattressInventory->total_quantity) }}</span>
                    </div>
                </div>

                <div class="w-full bg-white/[0.06] rounded-full h-1.5 overflow-hidden">
                    @php
                        $mattressPct = $mattressInventory->total_quantity > 0 ? round(($mattressInventory->allocated_quantity / $mattressInventory->total_quantity) * 100) : 0;
                    @endphp
                    <div class="bg-emerald-400 h-full rounded-full transition-all duration-700" style="width: {{ $mattressPct }}%"></div>
                </div>
                <div class="flex justify-between items-center text-[11px] text-slate-400 mt-2">
                    <span>{{ $mattressPct }}% allocated to block proctors</span>
                    <span>{{ 100 - $mattressPct }}% unassigned reserve</span>
                </div>
            </div>

            <!-- Pillow Card -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 relative overflow-hidden group">
                <div class="flex items-start justify-between relative z-10 mb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold text-lg shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-cyan-400">Warehouse Inventory</span>
                            <h3 class="text-lg font-bold text-white">{{ $pillowInventory->item_name }}</h3>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-medium px-3 py-1 rounded-full bg-white/[0.05] text-cyan-400 border border-white/10">
                        Penalty: ETB {{ number_format($pillowInventory->unit_penalty_charge, 2) }}
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center mb-4 relative z-10">
                    <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block tracking-wider">Available in Wh</span>
                        <span class="text-2xl font-bold text-white font-mono block mt-1">{{ number_format($pillowInventory->available_quantity) }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block tracking-wider">Allocated to Proctors</span>
                        <span class="text-2xl font-bold text-cyan-400 font-mono block mt-1">{{ number_format($pillowInventory->allocated_quantity) }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block tracking-wider">Total Stock</span>
                        <span class="text-2xl font-bold text-emerald-400 font-mono block mt-1">{{ number_format($pillowInventory->total_quantity) }}</span>
                    </div>
                </div>

                <div class="w-full bg-white/[0.06] rounded-full h-1.5 overflow-hidden">
                    @php
                        $pillowPct = $pillowInventory->total_quantity > 0 ? round(($pillowInventory->allocated_quantity / $pillowInventory->total_quantity) * 100) : 0;
                    @endphp
                    <div class="bg-cyan-400 h-full rounded-full transition-all duration-700" style="width: {{ $pillowPct }}%"></div>
                </div>
                <div class="flex justify-between items-center text-[11px] text-slate-400 mt-2">
                    <span>{{ $pillowPct }}% allocated to block proctors</span>
                    <span>{{ 100 - $pillowPct }}% unassigned reserve</span>
                </div>
            </div>
        </div>

        <!-- Proctors Current Stock Ledger Table -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-5 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Proctor Dedicated Stock Ledger</h3>
                    <p class="text-xs text-slate-400">Real-time balance of confirmed mattresses and pillows currently in custody of each block proctor</p>
                </div>
                <span class="text-xs font-mono text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20 font-medium">
                    {{ count($proctorStockStats) }} Authorized Proctors
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Proctor</th>
                            <th class="px-5 py-3.5">Assigned Block(s)</th>
                            <th class="px-5 py-3.5 text-center">Mattress Received</th>
                            <th class="px-5 py-3.5 text-center">Mattress Issued</th>
                            <th class="px-5 py-3.5 text-center">Mattress In Stock</th>
                            <th class="px-5 py-3.5 text-center">Pillow Received</th>
                            <th class="px-5 py-3.5 text-center">Pillow Issued</th>
                            <th class="px-5 py-3.5 text-center">Pillow In Stock</th>
                            <th class="px-5 py-3.5 text-right">Quick Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($proctorStockStats as $stat)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $stat['proctor']->avatar_url }}" alt="{{ $stat['proctor']->name }}" class="w-8 h-8 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <span class="font-semibold text-white block">{{ $stat['proctor']->name }}</span>
                                            <span class="text-[10px] text-slate-400">ID: {{ $stat['proctor']->university_id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                        {{ $stat['block_code'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono font-medium text-white">{{ $stat['mattress_total'] }}</td>
                                <td class="px-5 py-3.5 text-center font-mono text-amber-400">{{ $stat['mattress_issued'] }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full font-mono font-medium text-xs {{ $stat['mattress_available'] > 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $stat['mattress_available'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono font-medium text-white">{{ $stat['pillow_total'] }}</td>
                                <td class="px-5 py-3.5 text-center font-mono text-amber-400">{{ $stat['pillow_issued'] }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full font-mono font-medium text-xs {{ $stat['pillow_available'] > 0 ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $stat['pillow_available'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <button type="button" @click="transferModal = true" class="text-[11px] font-medium text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 px-3 py-1.5 rounded-xl border border-emerald-500/20 transition-all">
                                        + Transfer More
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-8 text-center text-slate-400">
                                    No proctors registered in system.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transfer History & Pending Handover Confirmations Table -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-5 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Manager-to-Proctor Transfer History</h3>
                    <p class="text-xs text-slate-400">Track initiated asset handovers, proctor verification statuses, and physical receipts</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manager.assets.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ !$statusFilter ? 'bg-white/10 text-white border border-white/10' : 'text-slate-400 hover:text-white bg-white/[0.04]' }}">All</a>
                    <a href="{{ route('manager.assets.index', ['status' => 'pending_confirmation']) }}" class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ $statusFilter === 'pending_confirmation' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:text-white bg-white/[0.04]' }}">Pending Acceptance</a>
                    <a href="{{ route('manager.assets.index', ['status' => 'confirmed']) }}" class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ $statusFilter === 'confirmed' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-white bg-white/[0.04]' }}">Confirmed</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Transfer Ref</th>
                            <th class="px-5 py-3.5">Proctor Recipient</th>
                            <th class="px-5 py-3.5">Block</th>
                            <th class="px-5 py-3.5">Item & Quantity</th>
                            <th class="px-5 py-3.5">Handover Status</th>
                            <th class="px-5 py-3.5">Transfer Date</th>
                            <th class="px-5 py-3.5">Confirmation Timestamp</th>
                            <th class="px-5 py-3.5">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($transfers as $tf)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 font-mono font-semibold text-white">#TR-{{ str_pad($tf->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-white block">{{ $tf->proctor?->name ?? 'Unknown' }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">ID: {{ $tf->proctor?->university_id }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-white font-medium">{{ $tf->block?->name ?? 'General Assignment' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-md font-mono font-medium text-xs bg-white/[0.05] text-white border border-white/10">
                                            {{ $tf->assigned_quantity }}x
                                        </span>
                                        <span class="font-semibold capitalize {{ $tf->item_type === 'mattress' ? 'text-emerald-400' : 'text-cyan-400' }}">
                                            {{ $tf->item_type }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($tf->status === 'confirmed')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5 w-fit">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Handover Accepted
                                        </span>
                                    @elseif($tf->status === 'pending_confirmation')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center gap-1.5 w-fit animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            Awaiting Proctor Verification
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center gap-1.5 w-fit">
                                            Handover Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 font-mono text-slate-400">{{ $tf->transfer_date?->format('M d, Y') }}</td>
                                <td class="px-5 py-3.5 font-mono text-[11px] text-slate-300">
                                    {{ $tf->confirmed_at ? $tf->confirmed_at->format('M d, Y H:i') : 'Pending verification' }}
                                </td>
                                <td class="px-5 py-3.5 text-[11px] text-slate-400 max-w-xs truncate">
                                    {{ $tf->notes ?: ($tf->rejection_reason ?: '—') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                    No asset transfers initiated yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transfers->hasPages())
                <div class="p-4 border-t border-white/[0.06]">
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>

        <!-- 1. Transfer Assets Modal -->
        <div x-show="transferModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             style="display: none;">
            <div @click.outside="transferModal = false" class="w-full max-w-lg rounded-3xl bg-[#121927] border border-white/10 shadow-2xl p-6">
                <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Transfer Assets to Proctor</h3>
                            <p class="text-xs text-slate-400">Initiate handover of mattresses or pillows to block proctor</p>
                        </div>
                    </div>
                    <button type="button" @click="transferModal = false" class="text-slate-400 hover:text-white transition-colors">&times;</button>
                </div>

                <form method="POST" action="{{ route('manager.assets.transfer') }}" class="space-y-4 mt-5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Select Proctor Recipient</label>
                        <select name="proctor_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                            <option value="">-- Choose Authorized Proctor --</option>
                            @foreach($proctors as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->name }} (ID: {{ $pr->university_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Target Dormitory Block (Optional)</label>
                        <select name="block_id" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                            <option value="">-- Auto-detect from Proctor Assignment --</option>
                            @foreach($blocks as $blk)
                                <option value="{{ $blk->id }}">{{ $blk->name }} ({{ $blk->block_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Item Type</label>
                            <select name="item_type" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                                <option value="mattress">Mattress (Wh: {{ $mattressInventory->available_quantity }} free)</option>
                                <option value="pillow">Pillow (Wh: {{ $pillowInventory->available_quantity }} free)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Transfer Quantity</label>
                            <input type="number" name="assigned_quantity" min="1" max="1000" value="100" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Transfer Notes / Intake Batch</label>
                        <textarea name="notes" rows="2" placeholder="e.g. 100 mattresses for freshman block placement" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                        <button type="button" @click="transferModal = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                            Initiate Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Restock Warehouse Modal -->
        <div x-show="stockModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             style="display: none;">
            <div @click.outside="stockModal = false" class="w-full max-w-md rounded-3xl bg-[#121927] border border-white/10 shadow-2xl p-6">
                <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                    <div>
                        <h3 class="text-base font-semibold text-white">Receive Shipment / Restock</h3>
                        <p class="text-xs text-slate-400">Add new mattresses or pillows to university warehouse stock</p>
                    </div>
                    <button type="button" @click="stockModal = false" class="text-slate-400 hover:text-white transition-colors">&times;</button>
                </div>

                <form method="POST" action="{{ route('manager.assets.stock_update') }}" class="space-y-4 mt-5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Asset Item</label>
                        <select name="item_code" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                            <option value="MATTRESS">Mattress (Current Total: {{ $mattressInventory->total_quantity }})</option>
                            <option value="PILLOW">Pillow (Current Total: {{ $pillowInventory->total_quantity }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">New Received Units</label>
                        <input type="number" name="additional_quantity" min="1" max="5000" value="500" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Standard Replacement Penalty (ETB)</label>
                        <input type="number" step="0.01" name="unit_penalty_charge" placeholder="Leave blank to keep existing rate" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                        <button type="button" @click="stockModal = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                            Add to Warehouse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>