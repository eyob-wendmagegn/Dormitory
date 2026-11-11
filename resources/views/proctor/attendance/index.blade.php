<x-app-layout title="Curfew Attendance Rounds">
    @php
        $block = $rosterData['block'];
        $curfew = $rosterData['curfew'];
        $submission = $rosterData['submission'];
        $summary = $rosterData['summary'];
        $isFinalized = $rosterData['is_finalized'];
        $rooms = $rosterData['rooms'];
        $floors = $rosterData['floors'] ?? [];
        $initialRoomId = $roomId ?? ($rooms[0]['id'] ?? null);
    @endphp

    <div class="space-y-6" x-data="proctorAttendance({
        blockId: {{ $selectedBlockId }},
        date: '{{ $date }}',
        initialRoomId: {{ $initialRoomId ?? 'null' }},
        saveUrl: '{{ route('proctor.attendance.save_room') }}',
        csrfToken: '{{ csrf_token() }}'
    })">
        <!-- 1. Top Control Bar: Curfew Status, Date & Block Selector, Online/Offline Indicator -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Left: Title, Curfew Status & Offline Pill -->
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 {{ $curfew['is_active'] ? 'animate-ping' : '' }}"></span>
                            Curfew Window: {{ $curfew['window'] }}
                        </span>

                        <!-- Network / Offline Mode Pill -->
                        <span :class="isOnline ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20 animate-pulse'"
                              class="px-3 py-1 rounded-full text-xs font-medium border flex items-center gap-2">
                            <span :class="isOnline ? 'bg-emerald-400' : 'bg-amber-400'" class="w-2 h-2 rounded-full"></span>
                            <span x-text="isOnline ? 'Online (Synced)' : 'Offline Mode (Device Saved)'"></span>
                        </span>

                        <!-- Pending Offline Sync Button -->
                        <button type="button"
                                x-show="offlineQueue.length > 0"
                                @click="syncOfflineQueue()"
                                style="display: none;"
                                class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all flex items-center gap-1.5 shadow-md">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Sync Pending (<span x-text="offlineQueue.length"></span>)</span>
                        </button>

                        @if($isFinalized)
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Daily Record Saved</span>
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                Rounds In Progress
                            </span>
                        @endif

                        <span class="text-xs text-slate-400 font-mono">
                            System Time: {{ $curfew['current_time'] }}
                        </span>
                    </div>

                    <h2 class="text-2xl font-bold tracking-tight text-white">
                        {{ $block->name }} ({{ $block->block_code }}) &bull; Nightly Roster
                    </h2>
                    <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">
                        Inspect room-by-room. Verify student presence and check photos. Approved gate passes are automatically highlighted as "On Leave".
                    </p>
                </div>

                <!-- Right: Date & Block Switcher Form -->
                <form method="GET" action="{{ route('proctor.attendance.index') }}" class="flex flex-wrap items-center gap-3">
                    @if($managedBlocks->count() > 1)
                        <div>
                            <label class="block text-[10px] uppercase font-semibold tracking-wider text-slate-400 mb-1">Block</label>
                            <select name="block_id" onchange="this.form.submit()" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 text-xs font-medium text-white rounded-xl px-3.5 py-2.5 focus:outline-none transition-all">
                                @foreach($managedBlocks as $b)
                                    <option value="{{ $b->id }}" {{ $b->id == $selectedBlockId ? 'selected' : '' }}>
                                        {{ $b->name }} ({{ $b->block_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="block_id" value="{{ $selectedBlockId }}">
                    @endif

                    <div>
                        <label class="block text-[10px] uppercase font-semibold tracking-wider text-slate-400 mb-1">Attendance Date</label>
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 text-xs font-medium text-white rounded-xl px-3.5 py-2.5 focus:outline-none transition-all">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="p-2.5 rounded-xl bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 text-slate-300 hover:text-white transition-colors" title="Reload Roster">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Real-time Roster KPI Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400 block mb-0.5">Total Residents</span>
                <span class="text-2xl font-bold font-mono text-white block">{{ $summary['total'] }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Active in Block</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-emerald-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Present
                </span>
                <span class="text-2xl font-bold font-mono text-emerald-400 block" x-text="liveStats.present">{{ $summary['present'] }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">In Dorm Rooms</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-rose-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Absent
                </span>
                <span class="text-2xl font-bold font-mono text-rose-400 block" x-text="liveStats.absent">{{ $summary['absent'] }}</span>
                <span class="text-[10px] text-rose-400/80 mt-0.5 block">Logged Absent</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-amber-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> On Leave
                </span>
                <span class="text-2xl font-bold font-mono text-amber-400 block" x-text="liveStats.excused">{{ $summary['excused'] }}</span>
                <span class="text-[10px] text-amber-400/80 mt-0.5 block">Approved Gate Pass</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-cyan-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> Sick / Clinic
                </span>
                <span class="text-2xl font-bold font-mono text-cyan-400 block" x-text="liveStats.sick">{{ $summary['sick'] }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Medical Verified</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400 block flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Unrecorded
                </span>
                <span class="text-2xl font-bold font-mono text-slate-300 block" x-text="liveStats.pending">{{ $summary['pending'] }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Remaining Check</span>
            </div>
        </div>

        <!-- 3. Room Selection & Floor Filter Controls (Room-by-Room Walkthrough) -->
        <div class="p-5 rounded-2xl bg-[#111726]/70 border border-white/[0.06] shadow-lg space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Dropdown Room Selector & Floor Filter -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Floor Filter Dropdown -->
                    <div>
                        <label class="block text-[10px] uppercase font-semibold tracking-wider text-slate-400 mb-1">Filter Floor</label>
                        <select x-model="selectedFloor" @change="onFloorFilterChange()" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 text-xs font-medium text-white rounded-xl px-3 py-2 min-w-[130px] focus:outline-none transition-all">
                            <option value="all" class="bg-[#121927] text-white">All Floors</option>
                            @foreach($floors as $fl)
                                <option value="{{ $fl }}" class="bg-[#121927] text-white">Floor {{ $fl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Room Selection Dropdown -->
                    <div>
                        <label class="block text-[10px] uppercase font-semibold tracking-wider text-emerald-400 mb-1">Select Current Room</label>
                        <select x-model="activeRoomId" @change="onRoomDropdownChange()" class="bg-[#0F1420] border border-emerald-500/30 focus:border-emerald-500/60 text-xs font-semibold text-emerald-300 rounded-xl px-3 py-2 min-w-[220px] focus:outline-none transition-all">
                            @foreach($rooms as $rm)
                                <option value="{{ $rm['id'] }}" data-floor="{{ $rm['floor'] ?? 1 }}" class="bg-[#121927] text-white py-1">
                                    Room {{ $rm['room_number'] }} &bull; Floor {{ $rm['floor'] ?? 1 }} ({{ $rm['occupied'] }}/{{ $rm['capacity'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- View Mode Toggle -->
                    <div>
                        <label class="block text-[10px] uppercase font-semibold tracking-wider text-slate-400 mb-1">Display Mode</label>
                        <div class="flex items-center rounded-xl bg-[#0F1420] border border-white/[0.08] p-1">
                            <button type="button"
                                    @click="viewMode = 'focus'"
                                    :class="viewMode === 'focus' ? 'bg-white/10 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white'"
                                    class="px-3 py-1 rounded-lg text-xs transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Focus Room</span>
                            </button>
                            <button type="button"
                                    @click="viewMode = 'all'"
                                    :class="viewMode === 'all' ? 'bg-white/10 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white'"
                                    class="px-3 py-1 rounded-lg text-xs transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                <span>Show All</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step Navigator & Progress -->
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="goToPrevRoom()"
                            :disabled="!hasPrevRoom"
                            class="px-3.5 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 text-xs font-medium text-white transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-1.5">
                        &larr; <span>Prev</span>
                    </button>

                    <div class="px-3.5 py-2 rounded-xl bg-[#0F1420] border border-white/[0.08] text-xs font-mono font-bold text-emerald-400">
                        <span x-text="currentRoomIndex + 1"></span> / <span x-text="filteredRooms.length"></span>
                    </div>

                    <button type="button"
                            @click="goToNextRoom()"
                            :disabled="!hasNextRoom"
                            class="px-3.5 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 text-xs font-medium text-white transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-1.5">
                        <span>Next</span> &rarr;
                    </button>
                </div>
            </div>

            <!-- Floor Quick Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin text-xs">
                <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400 mr-1 shrink-0">By Floor:</span>
                <button type="button"
                        @click="selectedFloor = 'all'; onFloorFilterChange()"
                        :class="selectedFloor === 'all' ? 'bg-white/10 text-white font-medium border border-white/10' : 'bg-white/[0.03] text-slate-400 hover:text-white border border-white/[0.06]'"
                        class="px-3 py-1 rounded-xl transition-all whitespace-nowrap">
                    All Floors ({{ count($rooms) }})
                </button>
                @foreach($floors as $fl)
                    @php
                        $floorRoomsCount = collect($rooms)->where('floor', $fl)->count();
                    @endphp
                    <button type="button"
                            @click="selectedFloor = '{{ $fl }}'; onFloorFilterChange()"
                            :class="selectedFloor == '{{ $fl }}' ? 'bg-white/10 text-white font-medium border border-white/10' : 'bg-white/[0.03] text-slate-400 hover:text-white border border-white/[0.06]'"
                            class="px-3 py-1 rounded-xl transition-all whitespace-nowrap">
                        Floor {{ $fl }} ({{ $floorRoomsCount }})
                    </button>
                @endforeach
            </div>
        </div>

        <!-- 4. Room Roster Section (Room by Room / Focus Mode) -->
        <div class="space-y-6">
            @forelse($rooms as $room)
                <div class="rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 overflow-hidden transition-all"
                     x-show="viewMode === 'all' ? (selectedFloor === 'all' || selectedFloor == {{ $room['floor'] }}) : (activeRoomId == {{ $room['id'] }})"
                     x-data="{
                         roomPending: false,
                         roomId: {{ $room['id'] }},
                         roomNumber: '{{ $room['room_number'] }}',
                         prevRoomId: {{ $room['prev_room_id'] ?? 'null' }},
                         nextRoomId: {{ $room['next_room_id'] ?? 'null' }},
                         isLastRoom: {{ $room['is_last_room'] ? 'true' : 'false' }}
                     }"
                     id="room-block-{{ $room['id'] }}">
                    <!-- Room Header Bar with Quick Action -->
                    <div class="p-5 bg-white/[0.02] border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-[#0F1420] border border-white/10 flex items-center justify-center font-bold text-white font-mono text-sm">
                                {{ $room['room_number'] }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    <span>Room {{ $room['room_number'] }}</span>
                                    <span class="text-[10px] px-2.5 py-0.5 rounded-full font-mono bg-white/[0.04] text-slate-400 border border-white/[0.06]">
                                        Floor {{ $room['floor'] ?? '1' }}
                                    </span>
                                    <template x-if="isRoomSaved({{ $room['id'] }})">
                                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1 font-medium">
                                            <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Saved
                                        </span>
                                    </template>
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Occupancy: <span class="text-white font-medium">{{ $room['occupied'] }} / {{ $room['capacity'] }} Residents</span>
                                </p>
                            </div>
                        </div>

                        <!-- Quick Bulk Actions & Save Room Button -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <button type="button"
                                    @click="markAllInRoomPresent({{ $room['id'] }})"
                                    class="px-3.5 py-2 text-xs font-medium text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-xl transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Mark All Present</span>
                            </button>

                            <button type="button"
                                    @click="clearAllInRoom({{ $room['id'] }})"
                                    class="px-3.5 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-1.5"
                                    title="Leave students in this room unchecked / blank">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Reset Blank</span>
                            </button>

                            <!-- "Save Room" Button (Navigates directly to next room) -->
                            <button type="button"
                                    @click="saveRoomAttendance({{ $room['id'] }}, nextRoomId, isLastRoom)"
                                    :disabled="roomPending"
                                    class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2 rounded-xl text-xs shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-1.5 disabled:opacity-50">
                                <span x-show="!roomPending">Save Room & Next &rarr;</span>
                                <span x-show="roomPending" style="display: none;">Saving...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Students List in Room -->
                    <div class="divide-y divide-white/[0.04] p-3 sm:p-5 space-y-2.5">
                        @forelse($room['students'] as $student)
                            <div class="p-4 rounded-2xl bg-[#0F1420]/70 hover:bg-[#0F1420] border border-white/[0.05] transition-all flex flex-col md:flex-row md:items-center justify-between gap-4"
                                 :class="chipsOpen ? 'relative z-20' : ''"
                                 id="student-row-{{ $student['student_id'] }}"
                                 x-data="{
                                     currentStatus: '{{ $student['status'] }}',
                                     remarksText: '{{ addslashes($student['remarks'] ?? '') }}',
                                     studentId: {{ $student['student_id'] }},
                                     roomId: {{ $room['id'] }},
                                     hasLeave: {{ $student['has_leave'] ? 'true' : 'false' }},
                                     chipsOpen: false
                                 }">
                                <!-- Student Bio Info & Photo -->
                                <div class="flex items-center gap-3.5 min-w-[260px]">
                                    <!-- Photo Thumbnail with Verified Ring & Zoom Modal Trigger -->
                                    <div class="relative group cursor-pointer shrink-0"
                                         @click="openPhotoPreview('{{ $student['avatar_url'] }}', '{{ addslashes($student['name']) }}', '{{ $student['university_id'] }}', '{{ $room['room_number'] }}', '{{ $student['department'] }}', {{ $student['has_photo'] ? 'true' : 'false' }})">
                                        <img src="{{ $student['avatar_url'] }}" alt="{{ $student['name'] }}"
                                             class="w-12 h-12 rounded-xl object-cover border {{ $student['has_photo'] ? 'border-emerald-500/50 shadow-emerald-950/40 shadow-sm' : 'border-white/10' }}">
                                        @if($student['has_photo'])
                                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-[#121927] flex items-center justify-center text-[9px] text-slate-950 font-bold" title="Photo Attached">
                                                ✓
                                            </span>
                                        @else
                                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-amber-500 border-2 border-[#121927] flex items-center justify-center text-[8px] text-slate-950 font-bold" title="Photo Missing">
                                                !
                                            </span>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-sm font-semibold text-white truncate">{{ $student['name'] }}</h4>

                                            <!-- Chronic / Frequent Absentee Warning Indicator Badge -->
                                            @if(!empty($student['is_frequent_absentee']) && $student['absent_count_this_week'] >= 2)
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center gap-1 shadow-sm"
                                                      title="Student has multiple absences in recent curfew rounds">
                                                    <svg class="w-3 h-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>{{ $student['absent_count_this_week'] }}x Absent this week</span>
                                                </span>
                                            @endif

                                            @if($student['has_leave'])
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center gap-1">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    On Leave
                                                </span>
                                            @endif

                                            @if(!$student['has_photo'])
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                    No Photo
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-xs text-slate-400 flex flex-wrap items-center gap-2 mt-1">
                                            <span class="font-mono text-white font-medium">{{ $student['university_id'] }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $student['department'] }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $student['academic_year'] }}</span>
                                        </div>

                                        <!-- Approved Leave Banner if applicable -->
                                        @if($student['has_leave'] && $student['leave_request'])
                                            <div class="mt-2 p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300">
                                                <div class="font-semibold flex items-center gap-1">
                                                    <span>Verified Gate Pass: {{ $student['leave_request']['type'] }} (#LR-{{ $student['leave_request']['id'] }})</span>
                                                </div>
                                                <div class="text-amber-400/80 mt-0.5">
                                                    Valid {{ $student['leave_request']['start_date'] }} to {{ $student['leave_request']['end_date'] }} &bull; Dest: {{ $student['leave_request']['destination'] }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Status Selection Buttons -->
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 bg-[#0D111A] p-1 rounded-xl border border-white/[0.06]">
                                        <!-- Present -->
                                        <button type="button"
                                                @click="setStatus(studentId, roomId, 'present')"
                                                :class="currentStatus === 'present' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30 shadow-sm' : 'text-slate-400 hover:text-white border-transparent'"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Present</span>
                                        </button>

                                        <!-- Absent -->
                                        <button type="button"
                                                @click="setStatus(studentId, roomId, 'absent')"
                                                :class="currentStatus === 'absent' ? 'bg-rose-500/15 text-rose-400 border-rose-500/30 shadow-sm' : 'text-slate-400 hover:text-white border-transparent'"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Absent</span>
                                        </button>

                                        <!-- Excused / On Leave -->
                                        <button type="button"
                                                @click="setStatus(studentId, roomId, 'excused')"
                                                :class="currentStatus === 'excused' ? 'bg-amber-500/15 text-amber-400 border-amber-500/30 shadow-sm' : 'text-slate-400 hover:text-white border-transparent'"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>On Leave</span>
                                        </button>

                                        <!-- Sick / Medical -->
                                        <button type="button"
                                                @click="setStatus(studentId, roomId, 'sick')"
                                                :class="currentStatus === 'sick' ? 'bg-cyan-500/15 text-cyan-400 border-cyan-500/30 shadow-sm' : 'text-slate-400 hover:text-white border-transparent'"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            <span>Sick</span>
                                        </button>
                                    </div>

                                    <!-- Remarks Input with preset chips -->
                                    <div class="relative w-full sm:w-56">
                                        <div class="flex items-center gap-1.5">
                                            <input type="text"
                                                   x-model="remarksText"
                                                   @input="updateRemarks(studentId, remarksText)"
                                                   placeholder="Remarks / Note..."
                                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all">
                                            <button type="button"
                                                    @click="chipsOpen = !chipsOpen"
                                                    class="p-2 rounded-xl bg-white/[0.05] border border-white/10 text-slate-400 hover:text-white text-xs transition-all"
                                                    title="Quick chips">
                                                &#9776;
                                            </button>
                                        </div>

                                        <!-- Chip Dropdown -->
                                        <div x-show="chipsOpen"
                                             @click.outside="chipsOpen = false"
                                             class="absolute right-0 bottom-full mb-1 sm:bottom-auto sm:top-full sm:mt-1 w-52 rounded-2xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-2 z-50 space-y-1 text-xs"
                                             style="display: none;">
                                            <span class="block text-[10px] uppercase font-semibold text-slate-400 px-2 mb-1">Quick Reasons</span>
                                            <button type="button" @click="setRemark(studentId, 'Admitted to Clinic'); chipsOpen = false" class="w-full text-left px-2.5 py-1.5 rounded-lg hover:bg-white/[0.05] text-cyan-300 font-medium transition-colors">
                                                Admitted to Clinic
                                            </button>
                                            <button type="button" @click="setRemark(studentId, 'Late Arrival (Curfew)'); chipsOpen = false" class="w-full text-left px-2.5 py-1.5 rounded-lg hover:bg-white/[0.05] text-amber-300 font-medium transition-colors">
                                                Late Arrival
                                            </button>
                                            <button type="button" @click="setRemark(studentId, 'Gate Pass Verified'); chipsOpen = false" class="w-full text-left px-2.5 py-1.5 rounded-lg hover:bg-white/[0.05] text-purple-300 font-medium transition-colors">
                                                Gate Pass Verified
                                            </button>
                                            <button type="button" @click="setRemark(studentId, 'Unexcused Night Absence'); chipsOpen = false" class="w-full text-left px-2.5 py-1.5 rounded-lg hover:bg-white/[0.05] text-rose-300 font-medium transition-colors">
                                                Unexcused Absence
                                            </button>
                                            <button type="button" @click="setRemark(studentId, 'Library Late Study'); chipsOpen = false" class="w-full text-left px-2.5 py-1.5 rounded-lg hover:bg-white/[0.05] text-emerald-300 font-medium transition-colors">
                                                Library Late Study
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-xs text-slate-400">
                                No students currently allocated to this room.
                            </div>
                        @endforelse
                    </div>

                    <!-- Room Bottom Walkthrough Bar -->
                    <div class="p-4 bg-white/[0.02] border-t border-white/[0.06] flex items-center justify-between text-xs">
                        <span class="text-slate-400 text-xs">
                            Finished checking Room {{ $room['room_number'] }}? Click Save Room to advance.
                        </span>

                        <button type="button"
                                @click="saveRoomAttendance({{ $room['id'] }}, nextRoomId, isLastRoom)"
                                :disabled="roomPending"
                                class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2 rounded-xl text-xs shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-1.5 disabled:opacity-50">
                            <span x-show="!roomPending">Save Room & Next &rarr;</span>
                            <span x-show="roomPending" style="display: none;">Saving...</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07]">
                    <svg class="w-12 h-12 text-slate-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <p class="text-sm font-semibold text-white">No active student allocations in this block</p>
                    <p class="text-xs text-slate-400 mt-1">Please allocate students or select a different dormitory block.</p>
                </div>
            @endforelse
        </div>

        <!-- 5. Daily Attendance Record Saving Bar -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xl shadow-black/25">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-semibold tracking-wider text-slate-400">Daily Attendance Record</span>
                    @if($isFinalized)
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Saved at {{ $submission->submitted_at?->format('H:i, M d') }}
                        </span>
                    @endif
                </div>
                <h3 class="text-lg font-bold text-white tracking-tight">Save / Submit Daily Record</h3>
                <p class="text-xs text-slate-400 mt-0.5 max-w-xl leading-relaxed">
                    Saves and logs nightly curfew records in the system database for {{ $block->name }}. Records aggregate continuously for your Weekly &amp; Monthly reports submitted to the Proctor Manager.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button"
                        @click="openDailySaveModal = true"
                        class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-3 rounded-xl text-xs sm:text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <span>{{ $isFinalized ? 'Update / Re-Save Daily Record' : 'Save / Submit Daily Record' }}</span>
                </button>
            </div>
        </div>

        <!-- Save Daily Record Confirmation Modal -->
        <div x-show="openDailySaveModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
             style="display: none;">
            <div @click.outside="openDailySaveModal = false"
                 class="w-full max-w-lg rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Save Daily Attendance Record</h3>
                    </div>
                    <button type="button" @click="openDailySaveModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                </div>

                <!-- Summary Checklist -->
                <div class="grid grid-cols-4 gap-2.5 text-center">
                    <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block mb-0.5">Present</span>
                        <span class="text-base font-bold text-emerald-400 font-mono" x-text="liveStats.present">{{ $summary['present'] }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block mb-0.5">Absent</span>
                        <span class="text-base font-bold text-rose-400 font-mono" x-text="liveStats.absent">{{ $summary['absent'] }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block mb-0.5">Leave</span>
                        <span class="text-base font-bold text-amber-400 font-mono" x-text="liveStats.excused">{{ $summary['excused'] }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#0F1420] border border-white/[0.06]">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block mb-0.5">Sick</span>
                        <span class="text-base font-bold text-cyan-400 font-mono" x-text="liveStats.sick">{{ $summary['sick'] }}</span>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300">
                    <p class="font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Daily Attendance Database Logging:
                    </p>
                    <ul class="list-disc list-inside mt-1.5 space-y-1 text-emerald-400/80">
                        <li>All marked absent students (<span x-text="liveStats.absent" class="font-bold text-rose-300"></span>) are permanently preserved in the database.</li>
                        <li>Unrecorded students will default to Present for tonight's log.</li>
                        <li>Data automatically feeds into your Weekly &amp; Monthly reports on the Reports page.</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('proctor.attendance.finalize') }}" @submit="onDailySaveSubmit($event)" class="space-y-4">
                    @csrf
                    <input type="hidden" name="block_id" value="{{ $selectedBlockId }}">
                    <input type="hidden" name="date" value="{{ $date }}">
                    <input type="hidden" name="students_json" x-ref="studentsJson">

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Proctor Nightly Notes (Optional)</label>
                        <textarea name="notes" rows="3" placeholder="e.g. Completed room checks at 21:45. Hallway lights functional. Room 102 student sick."
                                  class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                        <button type="button" @click="openDailySaveModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] rounded-xl border border-white/10 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 text-xs rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                            Confirm &amp; Save Daily Record
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Student Photo Inspection Modal -->
        <div x-show="photoModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
             style="display: none;">
            <div @click.outside="photoModalOpen = false"
                 class="w-full max-w-sm rounded-3xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-6 text-center space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-white/[0.06]">
                    <span class="text-xs font-semibold uppercase tracking-wider text-white">Visual Identity Verification</span>
                    <button type="button" @click="photoModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">&times;</button>
                </div>

                <div class="relative mx-auto w-40 h-40 rounded-2xl overflow-hidden border-2 border-emerald-500/40 shadow-xl bg-[#0F1420]">
                    <img :src="photoModalData.url" :alt="photoModalData.name" class="w-full h-full object-cover">
                </div>

                <div>
                    <h3 class="text-base font-bold text-white" x-text="photoModalData.name"></h3>
                    <p class="text-xs text-emerald-400 font-mono mt-0.5" x-text="photoModalData.id"></p>
                    <p class="text-xs text-slate-400 mt-1">
                        <span x-text="photoModalData.dept"></span> &bull; Room <span class="text-white font-semibold" x-text="photoModalData.room"></span>
                    </p>

                    <template x-if="photoModalData.hasPhoto">
                        <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400 font-medium mt-2.5 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full">
                            ✓ Official Student Photo Attached
                        </span>
                    </template>
                    <template x-if="!photoModalData.hasPhoto">
                        <span class="inline-flex items-center gap-1.5 text-xs text-amber-400 font-medium mt-2.5 bg-amber-500/10 border border-amber-500/20 px-3 py-1 rounded-full">
                            ⚠️ Student has not attached profile photo
                        </span>
                    </template>
                </div>

                <button type="button" @click="photoModalOpen = false" class="w-full py-2.5 bg-white/[0.05] hover:bg-white/[0.09] text-xs font-medium text-white rounded-xl border border-white/10 transition-all">
                    Close Preview
                </button>
            </div>
        </div>
    </div>

    <!-- Alpine.js Component for Proctor Curfew Web App -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('proctorAttendance', (config) => ({
                blockId: config.blockId,
                date: config.date,
                saveUrl: config.saveUrl,
                csrfToken: config.csrfToken,

                // Active state
                activeRoomId: config.initialRoomId,
                selectedFloor: 'all',
                viewMode: 'focus', // 'focus' (room by room) or 'all'
                openDailySaveModal: false,

                // Network & Offline Queue
                isOnline: navigator.onLine,
                offlineQueue: [],
                savedRooms: new Set(),

                // Photo zoom modal
                photoModalOpen: false,
                photoModalData: {
                    url: '',
                    name: '',
                    id: '',
                    room: '',
                    dept: '',
                    hasPhoto: false
                },

                // In-memory student state
                studentsMap: {},

                liveStats: {
                    total: {{ $summary['total'] }},
                    present: {{ $summary['present'] }},
                    absent: {{ $summary['absent'] }},
                    excused: {{ $summary['excused'] }},
                    sick: {{ $summary['sick'] }},
                    pending: {{ $summary['pending'] }}
                },

                // All rooms metadata for fast sequential transitions
                allRoomsList: @json($rooms),

                init() {
                    // Load offline queue from localStorage
                    const savedQueue = localStorage.getItem('dorm_attendance_queue_' + this.blockId);
                    if (savedQueue) {
                        try { this.offlineQueue = JSON.parse(savedQueue); } catch (e) { this.offlineQueue = []; }
                    }

                    // Network listener
                    window.addEventListener('online', () => {
                        this.isOnline = true;
                        this.showToast('Network restored! Syncing offline records...', 'success');
                        this.syncOfflineQueue();
                    });
                    window.addEventListener('offline', () => {
                        this.isOnline = false;
                        this.showToast('Network disconnected. Offline mode active - changes will save to device.', 'warning');
                    });

                    // Populate initial states
                    @foreach($rooms as $room)
                        @foreach($room['students'] as $st)
                            this.studentsMap[{{ $st['student_id'] }}] = {
                                studentId: {{ $st['student_id'] }},
                                roomId: {{ $room['id'] }},
                                status: '{{ $st['status'] }}',
                                remarks: '{{ addslashes($st['remarks'] ?? '') }}'
                            };
                            @if($st['status'] !== 'pending')
                                this.savedRooms.add({{ $room['id'] }});
                            @endif
                        @endforeach
                    @endforeach

                    this.recalcStats();
                },

                get filteredRooms() {
                    if (this.selectedFloor === 'all') {
                        return this.allRoomsList;
                    }
                    return this.allRoomsList.filter(r => String(r.floor) === String(this.selectedFloor));
                },

                get currentRoomIndex() {
                    const rooms = this.filteredRooms;
                    return rooms.findIndex(r => r.id === Number(this.activeRoomId));
                },

                get hasPrevRoom() {
                    return this.currentRoomIndex > 0;
                },

                get hasNextRoom() {
                    const idx = this.currentRoomIndex;
                    return idx >= 0 && idx < this.filteredRooms.length - 1;
                },

                goToPrevRoom() {
                    if (this.hasPrevRoom) {
                        this.activeRoomId = this.filteredRooms[this.currentRoomIndex - 1].id;
                        this.scrollToActiveRoom();
                    }
                },

                goToNextRoom() {
                    if (this.hasNextRoom) {
                        this.activeRoomId = this.filteredRooms[this.currentRoomIndex + 1].id;
                        this.scrollToActiveRoom();
                    }
                },

                onFloorFilterChange() {
                    const rooms = this.filteredRooms;
                    if (rooms.length > 0) {
                        const currentInFloor = rooms.find(r => r.id === Number(this.activeRoomId));
                        if (!currentInFloor) {
                            this.activeRoomId = rooms[0].id;
                        }
                    }
                },

                onRoomDropdownChange() {
                    this.scrollToActiveRoom();
                },

                scrollToActiveRoom() {
                    this.$nextTick(() => {
                        const el = document.getElementById('room-block-' + this.activeRoomId);
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                },

                isRoomSaved(roomId) {
                    return this.savedRooms.has(roomId);
                },

                setStatus(studentId, roomId, status) {
                    const current = this.studentsMap[studentId]?.status;
                    const targetStatus = (current === status) ? 'pending' : status;

                    if (this.studentsMap[studentId]) {
                        this.studentsMap[studentId].status = targetStatus;
                    } else {
                        this.studentsMap[studentId] = {
                            studentId: studentId,
                            roomId: roomId,
                            status: targetStatus,
                            remarks: ''
                        };
                    }
                    this.recalcStats();

                    // Update UI component
                    const el = document.getElementById('student-row-' + studentId);
                    if (el && el._x_dataStack && el._x_dataStack[0]) {
                        el._x_dataStack[0].currentStatus = targetStatus;
                    }
                },

                setRemark(studentId, remark) {
                    if (this.studentsMap[studentId]) {
                        this.studentsMap[studentId].remarks = remark;
                    }
                    const el = document.getElementById('student-row-' + studentId);
                    if (el && el._x_dataStack && el._x_dataStack[0]) {
                        el._x_dataStack[0].remarksText = remark;
                    }
                },

                updateRemarks(studentId, text) {
                    if (this.studentsMap[studentId]) {
                        this.studentsMap[studentId].remarks = text;
                    }
                },

                markAllInRoomPresent(roomId) {
                    Object.values(this.studentsMap).forEach(s => {
                        if (s.roomId === roomId && s.status !== 'excused') {
                            if (this.studentsMap[s.studentId]) {
                                this.studentsMap[s.studentId].status = 'present';
                            }
                            const el = document.getElementById('student-row-' + s.studentId);
                            if (el && el._x_dataStack && el._x_dataStack[0]) {
                                el._x_dataStack[0].currentStatus = 'present';
                            }
                        }
                    });
                    this.recalcStats();
                    this.showToast('Marked all unexcused students in Room present.', 'success');
                },

                clearAllInRoom(roomId) {
                    Object.values(this.studentsMap).forEach(s => {
                        if (s.roomId === roomId && s.status !== 'excused') {
                            if (this.studentsMap[s.studentId]) {
                                this.studentsMap[s.studentId].status = 'pending';
                            }
                            const el = document.getElementById('student-row-' + s.studentId);
                            if (el && el._x_dataStack && el._x_dataStack[0]) {
                                el._x_dataStack[0].currentStatus = 'pending';
                            }
                        }
                    });
                    this.recalcStats();
                    this.showToast('Room attendance reset to blank.', 'info');
                },

                recalcStats() {
                    let present = 0, absent = 0, excused = 0, sick = 0, pending = 0;
                    Object.values(this.studentsMap).forEach(s => {
                        if (s.status === 'present') present++;
                        else if (s.status === 'absent') absent++;
                        else if (s.status === 'excused') excused++;
                        else if (s.status === 'sick') sick++;
                        else pending++;
                    });
                    this.liveStats.present = present;
                    this.liveStats.absent = absent;
                    this.liveStats.excused = excused;
                    this.liveStats.sick = sick;
                    this.liveStats.pending = pending;
                },

                async saveRoomAttendance(roomId, nextRoomId, isLastRoom) {
                    const roomStudents = Object.values(this.studentsMap).filter(s => s.roomId === roomId);
                    if (roomStudents.length === 0) return;

                    const payload = {
                        block_id: this.blockId,
                        room_id: roomId,
                        date: this.date,
                        students: roomStudents.map(s => ({
                            student_id: s.studentId,
                            status: s.status,
                            remarks: s.remarks
                        }))
                    };

                    // Mark locally as saved
                    this.savedRooms.add(roomId);

                    if (!navigator.onLine) {
                        // Offline Mode: Queue for later sync
                        this.queueOfflinePayload(payload);
                        this.showToast('Room saved locally (Offline mode).', 'warning');
                        this.advanceToNextRoom(nextRoomId, isLastRoom);
                        return;
                    }

                    try {
                        const response = await fetch(this.saveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json().catch(() => ({}));
                        if (response.ok) {
                            this.showToast('Room attendance saved!', 'success');
                            this.advanceToNextRoom(data.next_room_id ?? nextRoomId, data.is_last_room ?? isLastRoom);
                        } else {
                            const errMessage = data.error || data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Failed to save room attendance.');
                            this.showToast(errMessage, 'error');
                        }
                    } catch (e) {
                        console.warn('Network error, saving offline:', e);
                        this.queueOfflinePayload(payload);
                        this.showToast('Network drop: Room saved locally on device.', 'warning');
                        this.advanceToNextRoom(nextRoomId, isLastRoom);
                    }
                },

                advanceToNextRoom(nextRoomId, isLastRoom) {
                    if (nextRoomId) {
                        this.activeRoomId = nextRoomId;
                        this.scrollToActiveRoom();
                    } else if (isLastRoom) {
                        this.showToast('All rooms in this block checked! Ready to Save Daily Record.', 'success');
                    }
                },

                queueOfflinePayload(payload) {
                    this.offlineQueue.push(payload);
                    localStorage.setItem('dorm_attendance_queue_' + this.blockId, JSON.stringify(this.offlineQueue));
                },

                async syncOfflineQueue() {
                    if (this.offlineQueue.length === 0) return;

                    let successCount = 0;
                    const remainingQueue = [];

                    for (const payload of this.offlineQueue) {
                        try {
                            const res = await fetch(this.saveUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });
                            if (res.ok) {
                                successCount++;
                            } else {
                                remainingQueue.push(payload);
                            }
                        } catch (err) {
                            remainingQueue.push(payload);
                        }
                    }

                    this.offlineQueue = remainingQueue;
                    localStorage.setItem('dorm_attendance_queue_' + this.blockId, JSON.stringify(remainingQueue));

                    if (successCount > 0) {
                        this.showToast(`Synced ${successCount} offline room record(s) successfully!`, 'success');
                    }
                },

                onDailySaveSubmit(event) {
                    // Populate hidden input with ALL student records from memory so nothing is lost!
                    const records = Object.values(this.studentsMap).map(s => ({
                        student_id: s.studentId,
                        room_id: s.roomId,
                        status: s.status,
                        remarks: s.remarks
                    }));

                    this.$refs.studentsJson.value = JSON.stringify(records);
                },

                openPhotoPreview(url, name, id, room, dept, hasPhoto) {
                    this.photoModalData = { url, name, id, room, dept, hasPhoto };
                    this.photoModalOpen = true;
                },

                showToast(message, type = 'success') {
                    const toast = document.createElement('div');
                    let bgClasses = 'bg-[#121927]/95 border border-emerald-500/30 text-emerald-300';
                    if (type === 'error') {
                        bgClasses = 'bg-[#121927]/95 border border-rose-500/30 text-rose-300';
                    } else if (type === 'warning') {
                        bgClasses = 'bg-[#121927]/95 border border-amber-500/30 text-amber-300';
                    }

                    toast.className = `fixed bottom-5 right-5 z-50 ${bgClasses} backdrop-blur-xl text-xs font-semibold px-4 py-3 rounded-2xl shadow-2xl shadow-black/40 flex items-center gap-2.5 transition-all transform duration-300`;
                    toast.innerHTML = `<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> <span>${message}</span>`;
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.classList.add('opacity-0');
                        setTimeout(() => toast.remove(), 400);
                    }, 3000);
                }
            }));
        });
    </script>
</x-app-layout>
