<x-app-layout title="Student Dormitory Portal">
    <div class="space-y-5">
        <!-- 1. Top Student Header Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90">Student Resident Portal</span>
                    <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                        {{ $student->academic_year_label ?? 'Undergraduate' }} &bull; {{ $student->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Hello, {{ $student->name }}</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">
                    Student ID: <span class="text-emerald-400 font-medium font-mono">{{ $student->university_id }}</span>
                    @if($student->department)
                        &bull; Department: <span class="text-white font-medium">{{ $student->department }}</span>
                    @endif
                    @if($block)
                        &bull; Hall: <span class="text-cyan-400 font-medium">{{ $block->name }} ({{ $block->block_code }})</span>
                    @endif
                    @if($room)
                        &bull; Room: <span class="text-white font-semibold">{{ $room->room_number }}</span>
                    @else
                        &bull; Room: <span class="text-amber-400 font-medium">Awaiting Placement</span>
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 relative z-10">
                <a href="{{ route('student.attendance.index') }}" class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98] flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>My Attendance</span>
                </a>
                <a href="{{ route('student.leaves.index') }}" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Gate Pass / Leave</span>
                </a>
                <a href="#submit-comment" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Submit Feedback</span>
                </a>
            </div>
        </div>

        @if (!$student->has_custom_photo)
            <!-- Photo Requirement Notice -->
            <div class="p-5 rounded-2xl bg-amber-500/[0.08] backdrop-blur-md border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-semibold text-amber-200">Official Student Photo Attachment Required</h4>
                        <p class="text-[11px] text-slate-300 mt-0.5 leading-relaxed">Dormitory regulations require all resident students to attach a photo to their profile for night curfew checks and room verification by proctors.</p>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-semibold transition-all whitespace-nowrap shadow-md shadow-amber-500/20 flex items-center justify-center gap-1.5 shrink-0 active:scale-[0.98]">
                    <span>Upload Photo Now</span> &rarr;
                </a>
            </div>
        @endif

        @if($pendingAssetConfirmationsCount > 0)
            <!-- Stage 2 Pending Digital Handover Callout -->
            <div class="p-5 sm:p-6 rounded-2xl bg-cyan-500/[0.08] backdrop-blur-md border border-cyan-500/20 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center shrink-0 text-cyan-400 shadow-inner">
                        <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-cyan-400 uppercase tracking-wider">Stage 2 Digital Handover</span>
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-mono border border-cyan-500/30">
                                {{ $pendingAssetConfirmationsCount }} Item{{ $pendingAssetConfirmationsCount > 1 ? 's' : '' }} Awaiting Review
                            </span>
                        </div>
                        <h3 class="text-base font-semibold text-white mt-1">Dormitory Asset Handover Awaiting Your Confirmation</h3>
                        <p class="text-xs text-slate-300 mt-0.5 max-w-2xl leading-relaxed">
                            Your Block Proctor has issued dorm assets (Mattress / Pillow) to your account. Please inspect the items physically and click <span class="text-cyan-400 font-medium">Confirm</span> to complete the check-out handover.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('student.assets.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-cyan-400 hover:bg-cyan-300 rounded-xl transition-all shadow-lg shadow-cyan-500/20 flex items-center gap-2 active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Review & Confirm Now</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- ROW 1: Top 4 Live Student Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
            <!-- 1. My Curfew Check-ins -->
            <dashboard-chart
                variant="metric-sub"
                title="My Curfew Check-ins"
                value="{{ $checkinTotal }}"
                :sub-metrics='@json($checkinSubMetrics)'
            ></dashboard-chart>

            <!-- 2. Room & Bed Status Breakdown -->
            <dashboard-chart
                variant="donut-legend"
                title="Room & Bed Status"
                :labels='@json($bedBreakdownLabels)'
                :data='@json($bedBreakdownData)'
                :colors='@json($bedBreakdownColors)'
                :legend-items='@json($bedBreakdownLegend)'
            ></dashboard-chart>

            <!-- 3. Curfew Compliance & Room Load -->
            <dashboard-chart
                variant="progress-bars"
                title="Residence Compliance"
                :legend-items='@json($complianceLegend)'
                :progress-items='@json($complianceProgress)'
            ></dashboard-chart>

            <!-- 4. Residence Activity Summary -->
            <dashboard-chart
                variant="sparkline-rows"
                title="Residence Summary"
                :sparkline-rows='@json($residentSparklines)'
            ></dashboard-chart>
        </div>

        <!-- ROW 2: Full Width 30-Day Personal Attendance & Gate Pass Timeline -->
        <dashboard-chart
            variant="daily-multi-line"
            title="30-Day Attendance & Leave Timeline"
            subtitle="Daily record of your curfew roll-call check-ins and approved gate passes"
            :labels='@json($dailyLineLabels)'
            :data='@json($dailyAttendanceSeries)'
            :secondary-data='@json($dailyGatePassesSeries)'
            :legend-items='@json($dailyLineLegend)'
            dropdown-text="Last 30 Days"
        ></dashboard-chart>

        <!-- ROW 3: Bottom 3 Live Personal Activity Mini Bar Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
            <!-- Mini Bar 1: Curfew Roll-calls -->
            <dashboard-chart
                variant="stat-mini-bar"
                title="Curfew Roll-calls"
                subtitle="Total check-ins recorded"
                value="{{ $checkinTotal }}"
                :labels='@json($last7Labels)'
                :data='@json($miniBarData1)'
                :colors="['#06B6D4']"
                dropdown-text="Last 7 Days"
            ></dashboard-chart>

            <!-- Mini Bar 2: Gate Passes & Authorized Leaves -->
            <dashboard-chart
                variant="stat-mini-bar"
                title="Authorized Leaves"
                subtitle="{{ $pendingLeaves }} Pending • {{ $approvedLeaves }} Approved"
                value="{{ $onLeaveCount }}"
                :labels='@json($last7Labels)'
                :data='@json($miniBarData2)'
                :colors="['#F59E0B']"
                dropdown-text="Last 7 Days"
            ></dashboard-chart>

            <!-- Mini Bar 3: Feedback & Maintenance Inquiries -->
            <dashboard-chart
                variant="stat-mini-bar"
                title="Feedback Inquiries"
                subtitle="Submitted maintenance & requests"
                value="{{ $totalMyComments }}"
                :labels='@json($last7Labels)'
                :data='@json($miniBarData3)'
                :colors="['#F43F5E']"
                dropdown-text="Last 7 Days"
            ></dashboard-chart>
        </div>

        @if ($allocation)
            <!-- Active Allocation Quick Overview -->
            <div id="my-dorm" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <dashboard-stats title="Current Block" value="{{ $block->block_code }}" subtitle="{{ $block->name }}" color="emerald"></dashboard-stats>
                <dashboard-stats title="Room Number" value="{{ $room->room_number }}" subtitle="Assigned Room" color="white"></dashboard-stats>
                <dashboard-stats title="Room Capacity" value="{{ $room->capacity }} Beds" subtitle="{{ $roommates->count() + 1 }}/{{ $room->capacity }} Occupied" color="white"></dashboard-stats>
                <dashboard-stats title="Allocation Status" value="Active" subtitle="Checked In" color="secondary"></dashboard-stats>
            </div>

            <!-- Dormitory Room Details & Roommates Grid -->
            <div id="room-info" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Room Details Card -->
                <div class="p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Residence Details</h3>
                        <span class="text-[10px] text-slate-400 font-mono">Room Capacity: {{ $room->capacity }} Beds</span>
                    </div>
                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#0F1420]/60 border border-white/[0.05]">
                            <span class="text-slate-400">Dormitory Hall</span>
                            <span class="font-semibold text-white">{{ $block->name }} ({{ $block->block_code }})</span>
                        </div>
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#0F1420]/60 border border-white/[0.05]">
                            <span class="text-slate-400">Campus Location</span>
                            <span class="font-semibold text-white">{{ $block->location ?? 'Main Campus' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#0F1420]/60 border border-white/[0.05]">
                            <span class="text-slate-400">Allocated On</span>
                            <span class="font-semibold text-white">{{ $allocation->allocated_at ? $allocation->allocated_at->format('M d, Y') : $allocation->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#0F1420]/60 border border-white/[0.05]">
                            <span class="text-slate-400">Allocating Proctor</span>
                            <span class="font-semibold text-emerald-400">{{ $allocation->allocator?->name ?? 'Dormitory Administration' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Assigned Roommates Card -->
                <div class="p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Your Roommates</h3>
                        <span class="text-[10px] text-slate-400">{{ $roommates->count() }} Roommates Assigned</span>
                    </div>
                    @if ($roommates->count() > 0)
                        <div class="space-y-2.5">
                            @foreach($roommates as $mate)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-[#0F1420]/60 border border-white/[0.05]">
                                    <div class="flex items-center gap-3">
                                        <img class="w-8 h-8 rounded-lg object-cover border border-white/10" src="{{ $mate->avatar_url }}" alt="{{ $mate->name }}" />
                                        <div>
                                            <span class="font-semibold text-white text-xs block">{{ $mate->name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $mate->academic_year_label ?? 'Student' }} &bull; {{ $mate->department ?? 'General' }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Active Roommate
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-xs text-slate-400">No other roommates assigned to your room at this moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Attractive Empty State when student has not yet been allocated -->
            <div id="my-dorm" class="p-7 sm:p-8 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] text-center max-w-3xl mx-auto shadow-xl">
                <div class="w-16 h-16 rounded-2xl bg-white/[0.05] border border-white/10 flex items-center justify-center text-emerald-400 mx-auto mb-4 shadow-lg shadow-emerald-500/10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Room Allocation In Progress</h3>
                <p class="text-xs text-slate-400 leading-relaxed max-w-md mx-auto">
                    You have not yet been assigned to a dormitory room for the current semester. Your block proctor is preparing room placements based on academic year and university intake schedules.
                </p>
                <div class="mt-6 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Status: Eligible &bull; Awaiting Proctor Placement</span>
                </div>
            </div>
        @endif

        <!-- ROW 4: Dorm Assets & Clearance Status + Proctor Notices -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            <!-- Left: My Dorm Assets & Clearance Status -->
            <div class="p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white tracking-tight">My Dormitory Assets</h3>
                                <p class="text-[11px] text-slate-400">Mattress & Pillow check-out status & return clearance</p>
                            </div>
                        </div>
                        <a href="{{ route('student.assets.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                            <span>Asset Portal</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <!-- Clearance Eligibility Banner -->
                    <div class="mt-4 p-3.5 rounded-xl border flex items-center justify-between {{ $isClearanceEligible ? 'bg-emerald-500/[0.08] border-emerald-500/20 text-emerald-300' : 'bg-amber-500/[0.08] border-amber-500/20 text-amber-300' }}">
                        <div class="flex items-center gap-2.5">
                            @if($isClearanceEligible)
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <span class="text-xs font-semibold block text-white">Eligible for Clearance</span>
                                    <span class="text-[10px] text-slate-300">All items accounted for and no pending penalty balance.</span>
                                </div>
                            @else
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <span class="text-xs font-semibold block text-white">Active Dorm Assets Held</span>
                                    <span class="text-[10px] text-slate-300">Return items to proctor at end-of-semester for final clearance.</span>
                                </div>
                            @endif
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-medium uppercase tracking-wider {{ $isClearanceEligible ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                            {{ $isClearanceEligible ? 'Eligible' : 'In Custody' }}
                        </span>
                    </div>

                    <!-- Issued Items List -->
                    <div class="mt-4 space-y-2.5">
                        @forelse($activeStudentAssets as $ast)
                            <div class="p-3 rounded-xl bg-[#0F1420]/60 border border-white/[0.05] flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white/[0.05] border border-white/10 flex items-center justify-center text-emerald-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold text-white capitalize block">{{ $ast->item_type }}</span>
                                        <span class="text-[10px] text-slate-400">Quantity: {{ $ast->quantity }} &bull; Issued: {{ $ast->issue_date ? $ast->issue_date->format('M d, Y') : 'Active' }}</span>
                                    </div>
                                </div>
                                <div>
                                    @if($ast->status === 'pending_confirmation')
                                        <a href="{{ route('student.assets.index') }}" class="px-2.5 py-1 text-[10px] font-semibold text-slate-950 bg-cyan-400 hover:bg-cyan-300 rounded-md transition-colors animate-pulse">
                                            Confirm Receipt
                                        </a>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Active
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-slate-400 bg-[#0F1420]/60 rounded-xl border border-white/[0.05]">
                                <p class="text-white font-medium mb-0.5">No Assets Currently Issued</p>
                                <p class="text-[11px] text-slate-500">Your proctor will issue mattress and pillow upon arrival at your room.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-[11px] text-slate-400">
                    <span>Stage 3 Return Policy:</span>
                    <a href="{{ route('student.assets.index') }}" class="text-cyan-400 hover:text-cyan-300 font-medium">
                        View Return Procedures &rarr;
                    </a>
                </div>
            </div>

            <!-- Right: Dorm Notices Bulletin -->
            <div class="p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white tracking-tight">Dormitory Bulletin Board</h3>
                                <p class="text-[11px] text-slate-400">Announcements from your block proctor & housing department</p>
                            </div>
                        </div>
                        <a href="{{ route('student.notices.index') }}" class="text-xs font-medium text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                            <span>All Notices</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <div class="mt-3 space-y-2.5">
                        @forelse($studentTargetedNotices as $notice)
                            <div class="p-3.5 rounded-xl bg-[#0F1420]/60 border border-white/[0.05] hover:border-white/[0.1] transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-xs font-semibold text-white truncate">{{ $notice->title }}</h4>
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-medium uppercase shrink-0 {{ $notice->priority === 'critical' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($notice->priority === 'urgent' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20') }}">
                                        {{ $notice->priority }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 line-clamp-2 mt-1 leading-relaxed">{{ $notice->summary ?: Str::limit($notice->content, 110) }}</p>
                                <div class="mt-2 flex items-center justify-between text-[10px] text-slate-500">
                                    <span>From {{ $notice->author->name ?? 'Block Proctor' }}</span>
                                    <span>{{ $notice->published_at ? $notice->published_at->diffForHumans() : 'Recently' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-slate-400">
                                <p class="text-white font-medium mb-1">No Active Notices</p>
                                <p class="text-[11px] text-slate-500">Block memos, curfew schedules, and inspection notices will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-[11px] text-slate-400">
                    <span>Targeted: {{ $block ? $block->block_code . ' Residents' : 'All Residents' }}</span>
                    <a href="{{ route('student.notices.index') }}" class="text-emerald-400 hover:text-emerald-300 font-medium">
                        Open Bulletin Feed &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Submit Feedback / Comment Form -->
        <div id="submit-comment" class="p-5 sm:p-6 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-emerald-400 mb-4">Submit Feedback or Maintenance Request</h3>
            <form method="POST" action="{{ route('student.comments.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Issue Category</label>
                        <select name="category" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                            <option value="maintenance">Maintenance / Repair (Plumbing, Electrical, Furniture)</option>
                            <option value="complaint">Dormitory Complaint</option>
                            <option value="inquiry">General Inquiry</option>
                            <option value="suggestion">Improvement Suggestion</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Title / Brief Subject</label>
                        <input type="text" name="title" required placeholder="e.g. Study hall light bulb flickering" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Detailed Message</label>
                    <textarea name="message" rows="3" required placeholder="Provide specific room or floor details so proctors can assist quickly..." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98]">
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>

        <!-- Student's Previous Feedback Tracking (Table Format) -->
        <div id="my-comments" class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">My Comments & Feedback Inquiries</h3>
                    <p class="text-xs text-slate-400">Track responses and official resolutions from block proctors and administration in table format</p>
                </div>
                <a href="{{ route('student.comments.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                    <span>View All Inquiries</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.03] text-slate-400 uppercase text-[10px] font-semibold border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Subject & Details</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Official Response</th>
                            <th class="px-5 py-3.5">Submitted</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($myComments as $cm)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider bg-white/[0.05] text-slate-300 border border-white/10">
                                        {{ $cm->category }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 max-w-sm">
                                    <span class="font-medium text-white block">{{ $cm->title }}</span>
                                    <span class="text-[11px] text-slate-400 line-clamp-2 mt-0.5 leading-relaxed">{{ $cm->message }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium capitalize {{ $cm->status === 'resolved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($cm->status === 'in_progress' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20') }}">
                                        {{ str_replace('_', ' ', $cm->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 max-w-xs">
                                    @if($cm->response)
                                        <div class="text-[11px]">
                                            <span class="text-emerald-400 font-medium block text-[10px]">
                                                {{ $cm->responder?->name ?? 'Dormitory Official' }}
                                                @if($cm->responded_at)
                                                    <span class="text-slate-400 font-normal">&bull; {{ $cm->responded_at->format('M d') }}</span>
                                                @endif
                                            </span>
                                            <p class="text-slate-200 line-clamp-2">{{ $cm->response }}</p>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-amber-400 italic bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/20">
                                            Under Review
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-[11px] text-slate-400">
                                    {{ $cm->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    <p class="font-medium text-white mb-1">No Inquiries Submitted</p>
                                    <p class="text-xs text-slate-500">You haven't submitted any room maintenance or housing inquiries yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
