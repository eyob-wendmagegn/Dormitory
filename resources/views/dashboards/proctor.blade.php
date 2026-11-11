<x-app-layout title="Block Proctor Dashboard">
    <div class="space-y-5">
        <!-- Top Bar (Field Operations Console) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90">Field Operations Console</span>
                    @if(!empty($assignedDepartments) || !empty($assignedYears))
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                            {{ count($assignedDepartments) }} Depts &bull; {{ count($assignedYears) }} Batches
                        </span>
                    @endif
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Welcome, Proctor {{ auth()->user()->name }}</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">
                    Managing Block: <span class="text-white font-semibold">{{ $managedBlocks->pluck('block_code')->join(', ') ?: 'Unassigned' }}</span>
                    @if($managedBlocks->isNotEmpty())
                        ({{ ucfirst($managedBlocks->first()->gender_category) }} Wing)
                    @endif
                    @if(!empty($assignedDepartments))
                        &bull; Depts: <span class="text-emerald-400 font-medium">{{ implode(', ', array_slice($assignedDepartments, 0, 3)) }}{{ count($assignedDepartments) > 3 ? '...' : '' }}</span>
                    @endif
                    @if(!empty($assignedYears))
                        &bull; Batches: <span class="text-emerald-400 font-medium">{{ implode(', ', array_map(fn($y) => "Yr {$y}", $assignedYears)) }}</span>
                    @endif
                    &bull; <span class="text-cyan-400 font-medium">{{ $stats['total_allocated'] }}/{{ $stats['total_capacity'] }} Beds &bull; {{ $stats['registered_rooms'] }} Reg Rooms &bull; {{ $stats['total_users'] }} System Users</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 relative z-10">
                <a href="{{ route('proctor.attendance.index') }}" class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98] flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Curfew Rounds</span>
                </a>
                <a href="{{ route('proctor.leaves.index') }}" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Gate Passes</span>
                </a>
                <a href="#quick-allocate" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>Allocate Student</span>
                </a>
                <a href="{{ route('proctor.students.index') }}" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                    <span>Student Directory</span>
                </a>
            </div>
        </div>

        @if($pendingHandoverCount > 0)
            <!-- Stage 1 Pending Handover Callout -->
            <div class="p-5 sm:p-6 rounded-2xl bg-amber-500/[0.08] backdrop-blur-md border border-amber-500/20 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center shrink-0 text-amber-400 shadow-inner">
                        <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider">Stage 1 Handover Notice</span>
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-mono border border-amber-500/30">
                                {{ $pendingHandoverCount }} Pending Transfer{{ $pendingHandoverCount > 1 ? 's' : '' }}
                            </span>
                        </div>
                        <h3 class="text-base font-semibold text-white mt-1">Warehouse Asset Transfers Awaiting Your Confirmation</h3>
                        <p class="text-xs text-slate-300 mt-0.5 max-w-2xl leading-relaxed">
                            The Proctor Manager has dispatched mattress/pillow consignments to your block. Please physically inspect items and click <span class="text-emerald-400 font-medium">Accept Handover</span> to log them into your current stock balance.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('proctor.assets.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-amber-400 hover:bg-amber-300 rounded-xl transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2 active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Accept Handover Now</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- ROW 1: Top 4 Live Field Analytics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
            <!-- 1. Curfew Check-ins Today -->
            <dashboard-chart
                variant="metric-sub"
                title="Curfew Check-ins Today"
                value="{{ $checkinTotal }}"
                :sub-metrics='@json($checkinSubMetrics)'
            ></dashboard-chart>

            <!-- 2. Bed Status Breakdown -->
            <dashboard-chart
                variant="donut-legend"
                title="Bed Status Breakdown"
                :labels='@json($bedBreakdownLabels)'
                :data='@json($bedBreakdownData)'
                :colors='@json($bedBreakdownColors)'
                :legend-items='@json($bedBreakdownLegend)'
            ></dashboard-chart>

            <!-- 3. Block Operations -->
            <dashboard-chart
                variant="progress-bars"
                title="Block Operations"
                :legend-items='@json($blockComplianceLegend)'
                :progress-items='@json($blockComplianceProgress)'
            ></dashboard-chart>

            <!-- 4. Managed Block Monitors -->
            <dashboard-chart
                variant="sparkline-rows"
                title="Managed Block Monitors"
                :sparkline-rows='@json($wingSparklines)'
            ></dashboard-chart>
        </div>

        <!-- ROW 2: Full Width 30-Day Curfew & Gate Pass Activity -->
        <dashboard-chart
            variant="daily-multi-line"
            title="30-Day Curfew & Gate Pass Activity"
            subtitle="Daily timeline of recorded curfew check-ins and approved gate passes in your managed blocks"
            :labels='@json($dailyLineLabels)'
            :data='@json($dailyCheckinsSeries)'
            :secondary-data='@json($dailyGatePassesSeries)'
            :legend-items='@json($dailyLineLegend)'
            dropdown-text="Last 30 Days"
        ></dashboard-chart>

        <!-- ROW 3: Bottom 3 Live Mini Bar Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
            <!-- Mini Bar 1: Allocated Residents -->
            <dashboard-chart
                variant="stat-mini-bar"
                title="Allocated Residents"
                subtitle="Active residents in managed blocks"
                value="{{ $stats['total_allocated'] }}"
                :labels='@json($last7Labels)'
                :data='@json($miniBarData1)'
                :colors="['#06B6D4']"
                dropdown-text="Last 7 Days"
            ></dashboard-chart>

            <!-- Mini Bar 2: Curfew Scans -->
            <dashboard-chart
                variant="stat-mini-bar"
                title="Curfew Scans (7d)"
                subtitle="Attendance logs recorded last 7 days"
                value="{{ $weeklyScansCount }}"
                :labels='@json($last7Labels)'
                :data='@json($miniBarData2)'
                :colors="['#F59E0B']"
                dropdown-text="Last 7 Days"
            ></dashboard-chart>

            <!-- Mini Bar 3: Active Gate Passes -->
            <dashboard-chart
                variant="stat-mini-bar"
                title="Active Gate Passes"
                subtitle="Approved leave permissions"
                value="{{ $activeLeavesCount }}"
                :labels='@json($last7Labels)'
                :data='@json($miniBarData3)'
                :colors="['#F43F5E']"
                dropdown-text="Last 7 Days"
            ></dashboard-chart>
        </div>

        <!-- ROW 4: Asset Inventory Stock & Manager Directives -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            <!-- Left: Proctor's Current Stock (Stage 2 & Stage 3) -->
            <div class="p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white tracking-tight">Proctor's Current Stock</h3>
                                <p class="text-[11px] text-slate-400">Dedicated inventory balance for your assigned block</p>
                            </div>
                        </div>
                        <a href="{{ route('proctor.assets.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                            <span>Manage Assets</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <!-- Mattresses Card -->
                        <div class="p-4 rounded-xl bg-[#0F1420]/60 border border-white/[0.05]">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-white">Mattresses</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ $proctorStock['mattresses_available'] }} In Stock
                                </span>
                            </div>
                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-2xl font-semibold tracking-tight text-white font-mono">{{ $proctorStock['mattresses_available'] }}</span>
                                <span class="text-[11px] text-slate-400">/ {{ $proctorStock['mattresses_total'] }} total</span>
                            </div>
                            <div class="mt-2 text-[11px] text-slate-400 flex items-center justify-between">
                                <span>Issued to Students:</span>
                                <span class="font-medium text-cyan-400">{{ $proctorStock['mattresses_issued'] }}</span>
                            </div>
                            <div class="mt-2 w-full bg-white/[0.06] h-1.5 rounded-full overflow-hidden">
                                @php
                                    $mattressPct = $proctorStock['mattresses_total'] > 0 ? min(100, round(($proctorStock['mattresses_issued'] / $proctorStock['mattresses_total']) * 100)) : 0;
                                @endphp
                                <div class="bg-gradient-to-r from-emerald-500 to-cyan-400 h-full rounded-full transition-all duration-700" style="width: {{ $mattressPct }}%"></div>
                            </div>
                        </div>

                        <!-- Pillows Card -->
                        <div class="p-4 rounded-xl bg-[#0F1420]/60 border border-white/[0.05]">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-white">Pillows</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ $proctorStock['pillows_available'] }} In Stock
                                </span>
                            </div>
                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-2xl font-semibold tracking-tight text-white font-mono">{{ $proctorStock['pillows_available'] }}</span>
                                <span class="text-[11px] text-slate-400">/ {{ $proctorStock['pillows_total'] }} total</span>
                            </div>
                            <div class="mt-2 text-[11px] text-slate-400 flex items-center justify-between">
                                <span>Issued to Students:</span>
                                <span class="font-medium text-cyan-400">{{ $proctorStock['pillows_issued'] }}</span>
                            </div>
                            <div class="mt-2 w-full bg-white/[0.06] h-1.5 rounded-full overflow-hidden">
                                @php
                                    $pillowPct = $proctorStock['pillows_total'] > 0 ? min(100, round(($proctorStock['pillows_issued'] / $proctorStock['pillows_total']) * 100)) : 0;
                                @endphp
                                <div class="bg-gradient-to-r from-emerald-500 to-cyan-400 h-full rounded-full transition-all duration-700" style="width: {{ $pillowPct }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Quick Actions:</span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('proctor.assets.index') }}" class="px-3.5 py-1.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-md shadow-emerald-500/20 flex items-center gap-1.5 active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Issue Asset</span>
                        </a>
                        <a href="{{ route('proctor.assets.clearance') }}" class="px-3.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Clearance Roster</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: Manager Directives & Internal Notices -->
            <div class="p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white tracking-tight">Manager Directives & Memos</h3>
                                <p class="text-[11px] text-slate-400">Internal announcements restricted to proctor personnel</p>
                            </div>
                        </div>
                        <a href="{{ route('proctor.notices.index') }}" class="text-xs font-medium text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                            <span>All Notices</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <div class="mt-3 space-y-2.5">
                        @forelse($managerNotices as $memo)
                            <div class="p-3.5 rounded-xl bg-[#0F1420]/60 border border-white/[0.05] hover:border-white/[0.1] transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-xs font-semibold text-white truncate">{{ $memo->title }}</h4>
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-medium uppercase shrink-0 {{ $memo->priority === 'critical' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($memo->priority === 'urgent' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20') }}">
                                        {{ $memo->priority }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 line-clamp-2 mt-1 leading-relaxed">{{ $memo->summary ?: Str::limit($memo->content, 110) }}</p>
                                <div class="mt-2 flex items-center justify-between text-[10px] text-slate-500">
                                    <span>By {{ $memo->author->name ?? 'Proctor Manager' }}</span>
                                    <span>{{ $memo->published_at ? $memo->published_at->diffForHumans() : 'Recently' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-slate-400">
                                <p class="text-white font-medium mb-1">No Active Manager Directives</p>
                                <p class="text-[11px] text-slate-500">All operational memos and guidelines will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Resident Notices:</span>
                    <a href="{{ route('proctor.notices.index') }}" class="px-3.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Post Student Notice</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Allocate Student Form Box -->
        <div id="quick-allocate" class="p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Allocate Student to Room</h3>
                    <p class="text-xs text-slate-400">Enforces room capacity and matches student gender to your dormitory block.</p>
                </div>
                <span class="text-xs font-medium px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                    Capacity Protected
                </span>
            </div>

            <form method="POST" action="{{ route('proctor.allocate.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Eligible Student</label>
                    <select name="student_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                        <option value="">-- Select Student --</option>
                        @forelse($eligibleStudents as $st)
                            <option value="{{ $st->id }}">
                                {{ $st->name }} ({{ $st->university_id }} &bull; {{ ucfirst($st->gender) }} &bull; {{ $st->department ?? 'General' }} &bull; {{ $st->academic_year ? $st->academic_year . 'th Yr' : 'Undergrad' }})
                            </option>
                        @empty
                            <option disabled value="">No unallocated students awaiting placement</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Available Room</label>
                    <select name="room_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                        <option value="">-- Select Room --</option>
                        @forelse($availableRooms as $rm)
                            <option value="{{ $rm->id }}">
                                Room {{ $rm->room_number }} ({{ $rm->block->name }} - {{ $rm->block->block_code }}) &bull; Free: {{ $rm->capacity - $rm->active_allocations_count }}/{{ $rm->capacity }}
                            </option>
                        @empty
                            <option disabled value="">No vacant rooms in assigned blocks</option>
                        @endforelse
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2.5 px-4 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98]">
                        + Confirm Allocation
                    </button>
                </div>
            </form>
        </div>

        <!-- Student Information in Managed Blocks (Full Width Table) -->
        <div id="student-information" class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl">
            <div class="p-5 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Student Information & Room Placements</h3>
                    <p class="text-xs text-slate-400">Students currently accommodated in your authorized dormitory blocks</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium px-3 py-1 rounded-full bg-white/[0.05] text-slate-300 border border-white/10 font-mono">
                        {{ $allocatedStudents->count() }} Active Residents
                    </span>
                    <a href="{{ route('proctor.students.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                        <span>Full Directory</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.03] text-slate-400 uppercase text-[10px] font-semibold border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Student</th>
                            <th class="px-5 py-3.5">University ID</th>
                            <th class="px-5 py-3.5">Gender</th>
                            <th class="px-5 py-3.5">Academic Year</th>
                            <th class="px-5 py-3.5">Assigned Residence</th>
                            <th class="px-5 py-3.5">Allocated Date</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($allocatedStudents as $alloc)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $alloc->student->avatar_url }}" alt="" class="w-7 h-7 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <span class="font-medium text-white block">{{ $alloc->student->name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $alloc->student->email ?? 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-emerald-400 font-mono">
                                    {{ $alloc->student->university_id }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase {{ $alloc->student->gender === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $alloc->student->gender }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-300">
                                    {{ $alloc->student->academic_year ? $alloc->student->academic_year . 'th Year' : 'Undergraduate' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-white font-medium">{{ $alloc->room->block->block_code }}</span> &bull; Room {{ $alloc->room->room_number }}
                                </td>
                                <td class="px-5 py-3.5 text-[11px] text-slate-400">
                                    {{ $alloc->allocated_at ? $alloc->allocated_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <form method="POST" action="{{ route('proctor.allocated.release', $alloc) }}" onsubmit="return confirm('Release accommodation for {{ $alloc->student->name }}?');" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-medium text-rose-400 hover:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 rounded-lg transition-colors">
                                            Release
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    <p class="font-medium text-white mb-1">No Active Student Allocations</p>
                                    <p class="text-xs text-slate-500">There are currently no students allocated to rooms in your assigned blocks.</p>
                                    <div class="mt-3">
                                        <a href="#quick-allocate" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all">
                                            Allocate First Student
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Managed Rooms Inventory Grid -->
        <div id="rooms" class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Room Inventory & Occupancy</h3>
                    <p class="text-xs text-slate-400">{{ $stats['registered_rooms'] }} Registered Rooms &bull; {{ $stats['available_beds'] }} Available Beds across assigned blocks</p>
                </div>
                <a href="{{ route('proctor.rooms.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                    <span>Manage Rooms</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @forelse($recentRooms as $rm)
                    <div class="p-4 rounded-xl bg-[#0F1420]/60 border border-white/[0.05] flex items-center justify-between hover:border-white/[0.1] transition-colors">
                        <div>
                            <span class="text-xs font-semibold text-white block">Room {{ $rm->room_number }} ({{ $rm->block->block_code }})</span>
                            <span class="text-[11px] text-slate-400">Capacity: {{ $rm->capacity }} Beds &bull; Occupied: {{ $rm->active_allocations_count }}</span>
                        </div>
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium capitalize {{ $rm->active_allocations_count < $rm->capacity ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                {{ $rm->active_allocations_count < $rm->capacity ? 'Available' : 'Full' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="col-span-3 text-center py-8 text-xs text-slate-500">No rooms registered in your assigned block yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
