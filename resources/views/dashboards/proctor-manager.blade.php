<x-app-layout title="Proctor Manager Dashboard">
    <div class="space-y-5">
        <!-- Top Overview Header Bar (Supervisory Console) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 relative overflow-hidden">
            <div class="relative z-10">
                <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90 mb-1 block">Supervisory Console</span>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Welcome, {{ auth()->user()->name }}</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">
                    {{ $stats['total_users'] }} Total Users &bull; {{ $stats['total_students'] }} Students &bull; {{ $stats['total_blocks'] }} Blocks &bull; {{ $stats['registered_rooms'] }} Registered Rooms
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 relative z-10">
                <a href="{{ route('manager.attendance.index') }}" class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98] flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Curfew Monitoring</span>
                </a>
                <a href="{{ route('manager.leaves.index') }}" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Gate Passes</span>
                </a>
                <a href="{{ route('manager.rooms.index') }}" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Room Inventory</span>
                </a>
                <a href="#proctor-assign" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Assign Proctor</span>
                </a>
                <a href="#blocks" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <span>Manage Blocks</span>
                </a>
            </div>
        </div>

        <!-- ROW 1: Dual Bar + Radial Gauge + Mini Cards & Supervision Sparkline -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <!-- 1. Dual Bar Chart (Col 5) -->
            <div class="lg:col-span-5 flex flex-col">
                <dashboard-chart
                    variant="dual-bar"
                    title="Block Bed Capacity & Occupancy"
                    subtitle="Live comparison of occupied and available beds across blocks"
                    :labels='@json($blockBarLabels)'
                    :datasets='@json($dualBarDatasets)'
                ></dashboard-chart>
            </div>

            <!-- 2. Radial Gauge (Col 4) -->
            <div class="lg:col-span-4 flex flex-col">
                <dashboard-chart
                    variant="radial-gauge"
                    title="Occupancy Utilization"
                    year-label="{{ now()->year }}"
                    subtitle="{{ $stats['occupancy_pct'] }}% Dormitory Capacity Utilized"
                    center-text="{{ $stats['occupancy_pct'] }}%"
                    center-label="Occupancy"
                    value="{{ $stats['total_allocated'] }} Active"
                    capacity-text="{{ number_format($stats['total_capacity']) }} Beds"
                ></dashboard-chart>
            </div>

            <!-- 3. Right Stack: 2 Mini Cards + Sparkline Supervision Report (Col 3) -->
            <div class="lg:col-span-3 flex flex-col justify-between gap-4">
                <!-- 2 Mini Squircle Stat Cards -->
                <div class="grid grid-cols-2 gap-3">
                    <!-- Staff Proctors -->
                    <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all duration-200 shadow-lg flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Staff Proctors</span>
                            <div class="text-2xl font-semibold tracking-tight text-white font-mono mt-0.5">{{ $stats['total_proctors'] }}</div>
                            <span class="text-[10px] font-medium text-emerald-400 flex items-center gap-1 mt-1 font-mono">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> {{ $stats['active_duty_proctors'] }} Assigned
                            </span>
                        </div>
                    </div>

                    <!-- Total Blocks -->
                    <div class="p-4 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all duration-200 shadow-lg flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div class="w-10 h-10 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-violet-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Blocks</span>
                            <div class="text-2xl font-semibold tracking-tight text-white font-mono mt-0.5">{{ $stats['total_blocks'] }}</div>
                            <span class="text-[10px] font-medium text-cyan-400 flex items-center gap-1 mt-1 font-mono">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> {{ $stats['active_blocks_pct'] }}% Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Sparkline Supervision Report -->
                <dashboard-chart
                    variant="spark-line"
                    title="Supervision Report"
                    badge="YEAR {{ now()->year }}"
                    subtitle="{{ $stats['supervision_pct'] }}% Block Coverage"
                    value="{{ $stats['total_students'] }} Students"
                    :labels='@json($supervisionLabels)'
                    :data='@json($supervisionLineData)'
                ></dashboard-chart>
            </div>
        </div>

        <!-- ROW 2: Allocation Statistics + Tabbed Area Wave + Active Proctor Assignments -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <!-- 5. Donut Stat: Allocation Statistics (Col 3) -->
            <div class="lg:col-span-3 flex flex-col">
                <dashboard-chart
                    variant="donut-stat"
                    title="Allocation Statistics"
                    subtitle="{{ $stats['total_students'] }} Registered Students"
                    value="{{ $stats['total_allocated'] }} Placed"
                    value-label="Active Placements"
                    center-text="{{ $stats['placement_pct'] }}%"
                    center-label="Placed"
                ></dashboard-chart>
            </div>

            <!-- 6. Tabbed Area Wave: Allocations, Leaves, Attendance (Col 5) -->
            <div class="lg:col-span-5 flex flex-col">
                <dashboard-chart
                    variant="area-wave"
                    title="Residential Flow Activity"
                    subtitle="Last 7 days resident timeline across dormitory wings"
                    metric-label="Total Accommodated"
                    value="{{ $stats['total_allocated'] }}"
                    badge="{{ $stats['placement_pct'] }}% Placement"
                    :labels='@json($waveLabels)'
                    :wave-tabs-data='@json($waveData)'
                ></dashboard-chart>
            </div>

            <!-- 7. Current Proctor Assignments + Quick Action CTA (Col 4) -->
            <div class="lg:col-span-4 p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] hover:border-white/[0.12] transition-all shadow-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-white tracking-tight">Proctor Assignments</h3>
                            <p class="text-[11px] text-slate-400">Active hall supervisory personnel ({{ $stats['supervision_pct'] }}% covered)</p>
                        </div>
                        <a href="{{ route('manager.assignments.index') }}" class="text-xs font-medium text-cyan-400 hover:text-cyan-300">All &rarr;</a>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($assignments as $idx => $asg)
                            @php
                                $colors = [
                                    ['bg' => 'bg-amber-500/10', 'border' => 'border-amber-500/20', 'text' => 'text-amber-400'],
                                    ['bg' => 'bg-cyan-500/10', 'border' => 'border-cyan-500/20', 'text' => 'text-cyan-400'],
                                    ['bg' => 'bg-violet-500/10', 'border' => 'border-violet-500/20', 'text' => 'text-violet-400'],
                                    ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/20', 'text' => 'text-emerald-400'],
                                ];
                                $c = $colors[$idx % 4];
                            @endphp
                            <div class="flex items-center justify-between p-3 rounded-xl bg-[#0F1420]/60 border border-white/[0.05] hover:border-white/[0.1] transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $c['bg'] }} border {{ $c['border'] }} flex items-center justify-center {{ $c['text'] }} font-semibold text-xs shrink-0">
                                        {{ strtoupper(substr($asg->proctor->name ?? 'P', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-white">{{ $asg->proctor->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $asg->block->name }} ({{ $asg->block->block_code }})</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">{{ $asg->assigned_at ? $asg->assigned_at->format('M d') : 'Active' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-slate-500">No active assignments found.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Sleek CTA button -->
                <div class="mt-4 pt-4 border-t border-white/[0.06] flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Supervise new hall</span>
                    <a href="#proctor-assign" class="px-4 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-md shadow-emerald-500/20 flex items-center gap-1.5 active:scale-[0.98]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>Assign Proctor</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ROW 3: Operational Section (Dormitory Blocks + Quick Assign Form) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <!-- Active Dormitory Blocks (Col 7) -->
            <div id="blocks" class="lg:col-span-7 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden flex flex-col justify-between shadow-xl">
                <div class="p-5 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-white tracking-tight">Active Dormitory Blocks & Room Capacity</h3>
                        <p class="text-xs text-slate-400">{{ $stats['registered_rooms'] }} Configured Rooms across {{ $stats['total_blocks'] }} Blocks &bull; Campus Standard Max: {{ number_format($stats['max_campus_beds']) }} Beds</p>
                    </div>
                    <span class="text-xs font-medium px-3 py-1 rounded-full bg-white/[0.05] border border-white/10 text-slate-300 font-mono">
                        {{ $stats['total_blocks'] }} of {{ $stats['max_campus_blocks'] }} Blocks ({{ number_format($stats['total_capacity']) }} Beds)
                    </span>
                </div>
                <div class="p-5 space-y-3">
                    @forelse($blocks as $b)
                        @php
                            $cap = ((int) ($b->rooms_sum_capacity ?? 0) > 0) ? (int) $b->rooms_sum_capacity : \App\Models\DormitoryBlock::STANDARD_BLOCK_CAPACITY;
                            $occ = (int) $b->active_allocations_count;
                            $avail = max(0, $cap - $occ);
                            $rate = $cap > 0 ? round(($occ / $cap) * 100, 1) : 0;
                        @endphp
                        <div class="p-3.5 rounded-xl bg-[#0F1420]/60 border border-white/[0.05] hover:border-white/[0.1] transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">{{ $b->block_code }}</span>
                                    <span class="text-xs sm:text-sm font-medium text-white">{{ $b->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ $b->rooms_count }} Rms)</span>
                                </div>
                                <span class="text-xs font-semibold text-slate-200 font-mono">{{ $occ }} / {{ $cap }} Beds</span>
                            </div>
                            <!-- Capacity Progress Bar -->
                            <div class="w-full h-2 rounded-full bg-white/[0.06] overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-cyan-400 rounded-full transition-all duration-700" style="width: {{ $rate > 0 ? min(100, max(5, $rate)) : 0 }}%"></div>
                            </div>
                            <div class="flex items-center justify-between mt-2 text-[11px] text-slate-400">
                                <span>{{ $avail }} beds remaining</span>
                                <span class="text-emerald-400 font-medium font-mono">{{ $rate }}% Occupied</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-500">
                            No dormitory blocks registered yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Proctor Assignment Form (Col 5) -->
            <div id="proctor-assign" class="lg:col-span-5 p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] flex flex-col justify-between shadow-xl">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-white tracking-tight">Assign Proctor to Dormitory Block</h3>
                            <p class="text-xs text-slate-400">Strict 1-to-1 assignment (one proctor per block)</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('manager.assignments.store') }}" class="space-y-4 mt-5">
                        @csrf
                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Select Dormitory Block</label>
                            <select name="block_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                <option value="">-- Select Dormitory Block --</option>
                                @forelse($blocks as $b)
                                    <option value="{{ $b->id }}">{{ $b->block_code }} - {{ $b->name }} ({{ ucfirst($b->gender_category) }})</option>
                                @empty
                                    <option disabled value="">No blocks registered</option>
                                @endforelse
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Select Proctor Personnel</label>
                            <select name="proctor_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                <option value="">-- Select Proctor Personnel --</option>
                                @forelse($proctors as $proc)
                                    @php
                                        $activeBlock = $proc->activeProctorAssignments->first()?->block;
                                    @endphp
                                    <option value="{{ $proc->id }}">
                                        {{ $proc->name }} ({{ $proc->university_id }}){{ $activeBlock ? ' • Assigned: ' . $activeBlock->block_code : '' }}
                                    </option>
                                @empty
                                    <option disabled value="">No proctor accounts found</option>
                                @endforelse
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Designated Scope</label>
                            <input type="text" readonly value="All Departments • Academic Year 1-7 (configure full scope in Assignments page)" class="w-full bg-[#0F1420]/50 border border-white/[0.06] rounded-xl px-3.5 py-2.5 text-xs text-slate-400 cursor-not-allowed">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-3 px-4 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 active:scale-[0.98]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                <span>Assign Proctor to Block</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
