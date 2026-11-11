<x-app-layout title="Administrator Dashboard">
    <div class="space-y-4 sm:space-y-5">

        @if (! empty($latestBackupFailure))
            <div class="p-4 sm:p-5 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-xs text-rose-300 shadow-lg backdrop-blur-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-rose-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-rose-200">PostgreSQL Backup / Restore Warning ({{ $latestBackupFailure->created_at->diffForHumans() }})</p>
                        <p class="text-rose-300/90 mt-0.5 leading-relaxed">
                            <span class="font-semibold">[{{ $latestBackupFailure->trigger_type }}]</span> {{ $latestBackupFailure->file_name }}: {{ \Illuminate\Support\Str::limit($latestBackupFailure->error_message, 180) }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.backups.index') }}" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 rounded-xl transition-all shrink-0 self-start sm:self-center">
                    View Backup Logs &rarr;
                </a>
            </div>
        @endif

        <!-- 1. TOP HEADER & QUICK STAT PILLS -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-48 h-48 bg-cyan-500/[0.04] rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(6,182,212,0.8)]"></span>
                    <span class="text-xs uppercase font-semibold tracking-wider text-slate-400">Executive Overview</span>
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-white">Administrator Dashboard</h2>
                <p class="text-sm text-slate-400 font-normal leading-relaxed mt-0.5">Here's what's going on across the university dormitory system today</p>
            </div>

            <!-- Summary Status Pill Badges -->
            <div class="flex flex-wrap items-center gap-3 relative z-10">
                <!-- Pill 1: Allocated (Emerald) -->
                <div class="px-4 py-2.5 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all duration-200 flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-white block font-mono">{{ $stats['allocated_students'] }} Allocated</span>
                        <span class="text-[11px] text-slate-400">Housed in rooms</span>
                    </div>
                </div>

                <!-- Pill 2: Unallocated (Amber) -->
                <div class="px-4 py-2.5 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all duration-200 flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-white block font-mono">{{ $stats['unallocated_students'] }} Unallocated</span>
                        <span class="text-[11px] text-slate-400">Pending placement</span>
                    </div>
                </div>

                <!-- Pill 3: Dorm Blocks (Cyan) -->
                <div class="px-4 py-2.5 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] transition-all duration-200 flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-white block font-mono">{{ $stats['total_blocks'] }} of {{ $stats['max_campus_blocks'] }} Blocks</span>
                        <span class="text-[11px] text-slate-400">{{ $stats['registered_rooms'] }} Reg &bull; Cap {{ number_format($stats['total_rooms']) }} Rms</span>
                    </div>
                </div>

                <!-- Quick Action: Curfew Logs (Primary CTA) -->
                <a href="{{ route('admin.attendance.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98] flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Curfew Logs</span>
                </a>

                <!-- Pill 4: Total Users (Violet / Cyan) -->
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-cyan-500/20 hover:border-cyan-400/40 transition-all duration-200 flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-white block font-mono">{{ $stats['total_users'] }} Total Users</span>
                        <span class="text-[11px] text-slate-400">{{ $stats['total_students'] }} Students &bull; {{ $stats['staff_count'] }} Staff &bull; {{ $stats['admin_count'] }} Admin</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- 2. MAIN ANALYTICS SECTION: WIDE GRAPH (LEFT) + 2x2 MINI CHARTS GRID (RIGHT) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

            <!-- LEFT COLUMN: LARGE MAIN ACTIVITY GRAPH -->
            <div class="lg:col-span-7 flex flex-col">
                <dashboard-chart
                    variant="main"
                    title="Total System Activity & Registrations"
                    subtitle="Daily timeline of student accounts, bed allocations, and curfew check-ins"
                    value="{{ $stats['total_users'] }}"
                    badge="{{ $stats['active_users'] }} Active Users"
                    date-range="{{ $dateRangeLabel }}"
                    :labels='@json($activityTrend["labels"])'
                    :datasets='@json($mainDatasets)'
                    :legend-items='@json($mainLegend)'
                ></dashboard-chart>
            </div>

            <!-- RIGHT COLUMN: 2x2 GRID OF 4 COMPACT CARDS -->
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                <!-- 1. Top-Left: Mini Bar Chart (Registered Rooms per Block) -->
                <dashboard-chart
                    variant="mini-bar"
                    title="Total Rooms"
                    subtitle="Design Cap: {{ number_format($stats['total_rooms']) }} ({{ $stats['total_blocks'] }} Blks)"
                    value="{{ $stats['registered_rooms'] }}"
                    badge="{{ $stats['available_rooms'] }} Available"
                    :labels='@json($miniBarData["labels"])'
                    :data='@json($miniBarData["values"])'
                    :legend-items='@json($miniBarLegend)'
                ></dashboard-chart>

                <!-- 2. Top-Right: Mini Zigzag Line (Student Registrations Last 7d) -->
                <dashboard-chart
                    variant="mini-zigzag"
                    title="New Students"
                    subtitle="Last 7 days • {{ $stats['total_students'] }} Total"
                    value="{{ $stats['new_students_7d'] }}"
                    badge="{{ $studentGrowthBadge }}"
                    :labels='@json($miniZigzagData["labels"])'
                    :data='@json($miniZigzagData["values"])'
                ></dashboard-chart>

                <!-- 3. Bottom-Left: Donut with Center Percentage -->
                <dashboard-chart
                    variant="donut-center"
                    title="Dorm Occupancy"
                    subtitle="Active Cap: {{ number_format($stats['total_capacity']) }} Beds"
                    center-text="{{ $stats['occupancy_rate'] }}%"
                    center-label="Occupied"
                    :labels='@json(["Allocated Beds", "Available Beds", "Unallocated Students"])'
                    :data='@json($donutData)'
                    :colors='@json(["#06B6D4", "#334155", "#8B5CF6"])'
                    :legend-items='@json($donutLegend)'
                ></dashboard-chart>

                <!-- 4. Bottom-Right: Semi-Circle Arc Gauge -->
                <dashboard-chart
                    variant="semi-gauge"
                    title="Gender Ratio"
                    subtitle="Registered Students ({{ $stats['total_students'] }})"
                    :labels='@json(["Male Students", "Female Students"])'
                    :data='@json([$stats["male_count"], $stats["female_count"]])'
                    :colors='@json(["#06B6D4", "#F43F5E"])'
                    :legend-items='@json($gaugeLegend)'
                ></dashboard-chart>

            </div>
        </div>

        <!-- 3. FULL-WIDTH DATA TABLE -->
        <div class="p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-base font-semibold text-white tracking-tight">Recent User Accounts & Resident Roster</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Live active student assignments, proctor staff, and authorization tiers ({{ $stats['total_users'] }} total accounts)</p>
                </div>

                <div class="flex items-center gap-2.5">
                    <a href="{{ route('admin.users.index') }}" class="text-xs font-medium text-slate-300 hover:text-white px-3.5 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 transition-all flex items-center gap-1.5">
                        <span>All Accounts ({{ $stats['total_users'] }})</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('admin.allocations.index') }}" class="text-xs font-medium text-slate-300 hover:text-white px-3.5 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 transition-all flex items-center gap-1.5">
                        <span>Room Allocations</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-400">
                    <thead class="bg-white/[0.02] text-slate-400 uppercase text-[10px] font-semibold border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3">User / Student</th>
                            <th class="px-5 py-3">ID Number</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Current Assignment</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($recentUsers as $u)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <!-- User Name + Avatar + Dept -->
                                <td class="px-5 py-3.5 font-medium text-white">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-white/[0.04] border border-white/[0.08] text-cyan-400 font-bold flex items-center justify-center text-xs shrink-0 font-mono">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="block font-semibold text-white text-xs">{{ $u->name }}</span>
                                            <span class="block text-[10px] text-slate-400 font-normal">{{ $u->department ?? 'General Roster' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- ID -->
                                <td class="px-5 py-3.5 font-mono text-cyan-400">{{ $u->university_id }}</td>

                                <!-- Role -->
                                <td class="px-5 py-3.5 capitalize font-medium text-slate-200">
                                    {{ str_replace('_', ' ', $u->role) }}
                                </td>

                                <!-- Assignment -->
                                <td class="px-5 py-3.5 font-mono">
                                    @if($u->activeAllocation)
                                        <span class="text-emerald-400 font-medium">
                                            {{ $u->activeAllocation->room->block->block_code ?? 'BLK' }} &bull; Rm {{ $u->activeAllocation->room->room_number ?? 'N/A' }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">Unassigned</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-3.5">
                                    @if($u->registration_status === 'completed')
                                        <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider inline-flex items-center gap-1">
                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            APPROVED
                                        </span>
                                    @else
                                        <span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider inline-flex items-center gap-1">
                                            PENDING
                                        </span>
                                    @endif
                                </td>

                                <!-- Joined Time -->
                                <td class="px-5 py-3.5 text-right text-[11px] text-slate-500 font-mono">
                                    {{ $u->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-500">No user records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. SECONDARY SECTION: DORMITORY BLOCKS PROGRESS + PRE-REGISTER USER FORM -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Active Dormitory Blocks Progress -->
            <div id="blocks" class="p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-white tracking-tight">Active Dormitory Blocks</h3>
                        <p class="text-xs text-slate-400">{{ $stats['total_blocks'] }} Registered Blocks &bull; {{ $stats['registered_rooms'] }} Rooms Configured &bull; Standard Max {{ number_format($stats['max_campus_beds']) }} Campus Beds</p>
                    </div>
                    <a href="{{ route('admin.blocks.index') }}" class="text-xs font-medium text-cyan-400 hover:text-cyan-300 transition-colors shrink-0 ml-2">
                        Manage ({{ $stats['total_blocks'] }}) &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recentBlocks as $blk)
                        @php
                            $sumCap = (int) ($blk->rooms_sum_capacity ?? 0);
                            $cap = $sumCap > 0 ? $sumCap : \App\Models\DormitoryBlock::STANDARD_BLOCK_CAPACITY;
                            $occ = (int) $blk->active_allocations_count;
                            $pct = $cap > 0 ? min(100, round(($occ / $cap) * 100, 1)) : 0;
                        @endphp
                        <div class="p-3.5 rounded-xl bg-[#0B0F17]/60 border border-white/[0.04] hover:border-white/[0.08] transition-all">
                            <div class="flex items-center justify-between text-xs font-medium mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-cyan-400 font-mono font-semibold">{{ $blk->block_code }}</span>
                                    <span class="text-white">{{ $blk->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ $blk->rooms_count }} Rms)</span>
                                </div>
                                <span class="text-emerald-400 font-mono">{{ $occ }} / {{ $cap }} Beds ({{ $pct }}%)</span>
                            </div>
                            <!-- Clean Progress Bar -->
                            <div class="w-full bg-white/[0.06] rounded-full h-1.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-cyan-500 to-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $pct > 0 ? max(4, $pct) : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-500">
                            No dormitory blocks registered yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Pre-Register Authorized User -->
            <div id="quick-preregister" class="p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-semibold text-white tracking-tight">Pre-Register Authorized User</h3>
                            <p class="text-xs text-slate-400">Add a pre-authorized record. The user will claim it during registration.</p>
                        </div>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20">
                            Admin Privilege
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.users.preregister') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mt-4">
                        @csrf
                        <div>
                            <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Full Name</label>
                            <input type="text" name="name" required placeholder="e.g. Dawit Kebede" class="w-full bg-[#0B0F17]/80 border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">University ID</label>
                            <input type="text" name="university_id" required placeholder="e.g. WLD/9901/16" class="w-full bg-[#0B0F17]/80 border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono placeholder-slate-500 transition-all" />
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Role</label>
                            <select name="role" class="w-full bg-[#0B0F17]/80 border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                <option value="student">Student</option>
                                <option value="proctor">Proctor</option>
                                <option value="proctor_manager">Proctor Manager</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Gender</label>
                            <select name="gender" class="w-full bg-[#0B0F17]/80 border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Department</label>
                            <input type="text" name="department" placeholder="e.g. Computer Science" class="w-full bg-[#0B0F17]/80 border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Academic Year</label>
                            <select name="academic_year" class="w-full bg-[#0B0F17]/80 border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                <option value="">None (Staff)</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                                <option value="5">5th Year</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 flex justify-end pt-2">
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98]">
                                + Pre-Register User
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
