<x-app-layout title="Allocate Student">
    <div class="space-y-6" x-data="{
        students: {{ Js::from($eligibleStudents->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'gender' => $s->gender,
            'department' => $s->department ?? 'General',
            'university_id' => $s->university_id,
            'year' => $s->academic_year ?? 1,
            'avatar_url' => $s->avatar_url
        ])) }},
        rooms: {{ Js::from($rooms->map(fn($r) => [
            'id' => $r->id,
            'room_number' => $r->room_number,
            'block_id' => $r->block_id,
            'block_code' => $r->block->block_code,
            'block_name' => $r->block->name,
            'block_gender' => $r->block->gender_category,
            'capacity' => $r->capacity,
            'available_beds' => $r->available_beds,
        ])) }},
        selectedStudentIds: {{ Js::from(request('student_id') ? [(int)request('student_id')] : (old('student_ids') ? array_map('intval', (array)old('student_ids')) : (old('student_id') ? [(int)old('student_id')] : []))) }},
        selectedRoomId: '{{ request('room_id', old('room_id', '')) }}',
        studentSearch: '',
        studentGenderFilter: '',
        studentDeptFilter: '',
        studentYearFilter: '',
        roomSearch: '',
        roomBlockFilter: '',

        get selectedStudents() {
            return this.students.filter(s => this.selectedStudentIds.includes(s.id));
        },
        get selectedStudent() {
            return this.selectedStudents.length > 0 ? this.selectedStudents[0] : null;
        },
        get selectedRoom() {
            return this.rooms.find(r => r.id == this.selectedRoomId);
        },
        get maxSelectable() {
            if (!this.selectedRoom) return 6;
            return Math.min(6, this.selectedRoom.available_beds || 6);
        },
        get isGenderMismatch() {
            if (this.selectedStudents.length === 0 || !this.selectedRoom) return false;
            return this.selectedStudents.some(s => s.gender !== this.selectedRoom.block_gender);
        },
        get hasMixedGenders() {
            if (this.selectedStudents.length <= 1) return false;
            const firstGender = this.selectedStudents[0].gender;
            return this.selectedStudents.some(s => s.gender !== firstGender);
        },
        get filteredStudents() {
            return this.students.filter(s => {
                const matchSearch = !this.studentSearch ||
                    s.name.toLowerCase().includes(this.studentSearch.toLowerCase()) ||
                    s.university_id.toLowerCase().includes(this.studentSearch.toLowerCase()) ||
                    (s.department && s.department.toLowerCase().includes(this.studentSearch.toLowerCase()));
                const matchGender = !this.studentGenderFilter || s.gender === this.studentGenderFilter;
                const matchDept = !this.studentDeptFilter || (s.department && s.department.toLowerCase() === this.studentDeptFilter.toLowerCase());
                const matchYear = !this.studentYearFilter || s.year == this.studentYearFilter;
                return matchSearch && matchGender && matchDept && matchYear;
            });
        },
        get filteredRooms() {
            return this.rooms.filter(r => {
                const matchSearch = !this.roomSearch ||
                    r.room_number.toLowerCase().includes(this.roomSearch.toLowerCase()) ||
                    r.block_code.toLowerCase().includes(this.roomSearch.toLowerCase()) ||
                    r.block_name.toLowerCase().includes(this.roomSearch.toLowerCase());
                const matchBlock = !this.roomBlockFilter || r.block_id == this.roomBlockFilter;
                return matchSearch && matchBlock;
            });
        },
        toggleStudent(id) {
            const index = this.selectedStudentIds.indexOf(id);
            if (index > -1) {
                this.selectedStudentIds.splice(index, 1);
            } else {
                const student = this.students.find(s => s.id === id);
                if (!student) return;

                if (this.selectedStudentIds.length >= this.maxSelectable) {
                    alert('You can select at most ' + this.maxSelectable + ' student(s) at once for this room (design limit is 6 students max).');
                    return;
                }

                if (this.selectedStudents.length > 0 && this.selectedStudents[0].gender !== student.gender) {
                    alert('All selected students in one batch must have the same gender (' + this.selectedStudents[0].gender + ').');
                    return;
                }

                if (this.selectedRoom && this.selectedRoom.block_gender !== student.gender) {
                    alert('Cannot select a ' + student.gender + ' student for a ' + this.selectedRoom.block_gender + ' block room.');
                    return;
                }

                this.selectedStudentIds.push(id);
            }
        },
        selectOnlyStudent(id) {
            this.selectedStudentIds = [id];
        },
        selectBatch(count) {
            const limit = Math.min(count || 6, this.maxSelectable);
            const targetGender = this.selectedRoom ? this.selectedRoom.block_gender : (this.selectedStudents.length > 0 ? this.selectedStudents[0].gender : null);
            
            const pool = this.filteredStudents.filter(s => !targetGender || s.gender === targetGender);
            this.selectedStudentIds = pool.slice(0, limit).map(s => s.id);
        },
        clearSelectedStudents() {
            this.selectedStudentIds = [];
        },
        selectRoom(id) {
            this.selectedRoomId = id;
            const room = this.rooms.find(r => r.id == id);
            if (room && this.selectedStudentIds.length > room.available_beds) {
                this.selectedStudentIds = this.selectedStudentIds.slice(0, room.available_beds);
            }
        }
    }">
        <!-- Top bar with link to Allocated Students -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Placement Engine</span>
                </div>
                <h2 class="text-xl font-bold text-white tracking-tight">Interactive Room Allocation Engine</h2>
                <p class="text-xs text-slate-400 mt-0.5">Assign students individually or in batches (up to 6 students at once) to authorized dormitory rooms.</p>
            </div>
            <a href="{{ route('proctor.allocated.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-medium rounded-xl bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 text-slate-300 hover:text-white transition-all shadow-sm">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>View Resident Allocations</span>
            </a>
        </div>

        <!-- Academic Scope & Gender Notice Banner -->
        <div class="p-4 rounded-2xl bg-[#121927]/80 backdrop-blur-md border-l-4 border-l-emerald-400 border border-white/[0.07] space-y-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 shrink-0 border border-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <strong class="text-white text-xs block font-semibold">Proctor Authorized Scope:</strong>
                        <span class="text-xs text-slate-400">
                            Block: <span class="text-emerald-400 font-semibold">{{ $blocks->pluck('block_code')->join(', ') ?: 'None' }}</span>
                            @if(!empty($blockGenders))
                                &bull; Wing: <span class="capitalize font-medium text-cyan-400">{{ implode(', ', $blockGenders) }}</span>
                            @endif
                            &bull; Departments: <span class="text-white font-medium">{{ count($assignedDepartments) > 0 ? implode(', ', $assignedDepartments) : 'All Departments' }}</span>
                            &bull; Batches: <span class="text-white font-medium">{{ count($assignedYears) > 0 ? implode(', ', array_map(fn($y) => "Yr {$y}", $assignedYears)) : 'All Batches' }}</span>
                        </span>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 shrink-0">
                    Eligible Students: <span class="text-emerald-400 font-semibold" x-text="students.length"></span> &bull; Free Rooms: <span class="text-emerald-400 font-semibold" x-text="rooms.length"></span>
                </div>
            </div>
        </div>

        <!-- Allocation Form -->
        <form action="{{ route('proctor.allocate.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Hidden inputs bound to Alpine selected IDs -->
            <template x-for="sId in selectedStudentIds" :key="sId">
                <input type="hidden" name="student_ids[]" :value="sId" />
            </template>
            <input type="hidden" name="student_id" :value="selectedStudentIds[0] || ''" />
            <input type="hidden" name="room_id" :value="selectedRoomId" required />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- ============================================== -->
                <!-- LEFT PANE: Search & Select Unallocated Students -->
                <!-- ============================================== -->
                <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col h-[620px]">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/[0.06]">
                        <div>
                            <h3 class="text-sm font-semibold text-white tracking-tight flex items-center gap-2">
                                <span>1. Select Students</span>
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono" x-text="filteredStudents.length + ' available'"></span>
                            </h3>
                            <p class="text-[11px] text-slate-400">Choose 1 or up to 6 students to assign together.</p>
                        </div>

                        <!-- Selection Counter & Batch Select Shortcuts -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="px-2.5 py-1 rounded-xl text-xs font-medium border transition-all"
                                  :class="selectedStudentIds.length > 0 ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-white/[0.03] text-slate-400 border-white/10'">
                                <span x-text="selectedStudentIds.length"></span> / <span x-text="maxSelectable"></span> selected
                            </span>
                            <button type="button" @click="selectBatch(maxSelectable)" class="px-2.5 py-1 text-[11px] font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-lg transition-colors" title="Select up to max allowed">
                                Select <span x-text="maxSelectable"></span>
                            </button>
                            <button type="button" x-show="selectedStudentIds.length > 0" @click="clearSelectedStudents()" class="px-2.5 py-1 text-[11px] font-medium text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 rounded-lg transition-colors">
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Selected Students Chips Bar -->
                    <div x-show="selectedStudents.length > 0" class="py-2.5 flex flex-wrap gap-1.5 border-b border-white/[0.06] max-h-20 overflow-y-auto">
                        <template x-for="st in selectedStudents" :key="st.id">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white/[0.05] border border-white/10 text-white">
                                <span class="w-1.5 h-1.5 rounded-full" :class="st.gender === 'female' ? 'bg-rose-400' : 'bg-cyan-400'"></span>
                                <span x-text="st.name"></span>
                                <button type="button" @click.stop="toggleStudent(st.id)" class="text-slate-400 hover:text-rose-400 ml-0.5">
                                    &times;
                                </button>
                            </span>
                        </template>
                    </div>

                    <!-- Student Search & Filters -->
                    <div class="pt-3 pb-3 space-y-2">
                        <div class="relative">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="studentSearch" placeholder="Search student name or ID..."
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white placeholder-slate-500" />
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <select x-model="studentDeptFilter" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-2.5 py-2 text-xs text-white">
                                <option value="">All Departments</option>
                                @php
                                    $deptList = !empty($assignedDepartments) ? $assignedDepartments : \App\Services\DepartmentCatalog::all();
                                @endphp
                                @foreach($deptList as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>

                            <select x-model="studentGenderFilter" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-2.5 py-2 text-xs text-white">
                                <option value="">All Genders</option>
                                <option value="male">Male Only</option>
                                <option value="female">Female Only</option>
                            </select>

                            <select x-model="studentYearFilter" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-2.5 py-2 text-xs text-white">
                                <option value="">All Batches</option>
                                @foreach(!empty($assignedYears) ? $assignedYears : [1, 2, 3, 4, 5, 6, 7] as $yr)
                                    <option value="{{ $yr }}">Year {{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Scrollable Student List Cards with Checkboxes -->
                    <div class="flex-1 overflow-y-auto space-y-2 pr-1 custom-scroll">
                        <template x-for="s in filteredStudents" :key="s.id">
                            <div @click="toggleStudent(s.id)"
                                 :class="selectedStudentIds.includes(s.id) ? 'border-emerald-500/50 bg-emerald-500/10 shadow-md ring-1 ring-emerald-500/30' : 'border-white/[0.06] bg-[#0F1420] hover:border-white/20 hover:bg-white/[0.03]'"
                                 class="p-3.5 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <img :src="s.avatar_url" alt="" class="w-9 h-9 rounded-xl object-cover border border-white/10" />
                                    <div>
                                        <span class="text-xs font-semibold text-white block" x-text="s.name"></span>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] font-mono text-emerald-400" x-text="s.university_id"></span>
                                            <span class="text-[10px] text-slate-400" x-text="'Year ' + s.year"></span>
                                            <span class="text-[10px] text-slate-400" x-show="s.department" x-text="'&bull; ' + s.department"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium capitalize"
                                          :class="s.gender === 'female' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20'"
                                          x-text="s.gender"></span>
                                    
                                    <!-- Selection Indicator / Checkbox -->
                                    <div class="w-5 h-5 rounded-lg border flex items-center justify-center transition-colors"
                                         :class="selectedStudentIds.includes(s.id) ? 'border-emerald-500 bg-emerald-500 text-slate-950 font-bold' : 'border-white/20 bg-white/[0.02] text-transparent'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="filteredStudents.length === 0" class="p-8 text-center text-slate-400 text-xs">
                            No unallocated students match your filter criteria.
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- RIGHT PANE: Search & Select Authorized Room    -->
                <!-- ============================================== -->
                <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col h-[620px]">
                    <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                        <div>
                            <h3 class="text-sm font-semibold text-white tracking-tight flex items-center gap-2">
                                <span>2. Select Room</span>
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono" x-text="filteredRooms.length + ' available'"></span>
                            </h3>
                            <p class="text-[11px] text-slate-400">Choose an available room in your assigned blocks.</p>
                        </div>
                        <template x-if="selectedRoom">
                            <span class="px-2.5 py-1 rounded-xl text-xs font-medium flex items-center gap-1.5"
                                  :class="selectedRoom.block_gender === 'female' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20'">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span x-text="selectedRoom.block_code + ' - Rm ' + selectedRoom.room_number"></span>
                            </span>
                        </template>
                    </div>

                    <!-- Room Search & Filter -->
                    <div class="pt-3 pb-3 space-y-2">
                        <div class="relative">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="roomSearch" placeholder="Search room number (e.g. 101) or block..."
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white placeholder-slate-500" />
                        </div>

                        <div>
                            <select x-model="roomBlockFilter" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white">
                                <option value="">All Authorized Blocks</option>
                                @foreach($blocks as $b)
                                    <option value="{{ $b->id }}">{{ $b->block_code }} - {{ $b->name }} ({{ ucfirst($b->gender_category) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Scrollable Room List Cards -->
                    <div class="flex-1 overflow-y-auto space-y-2 pr-1 custom-scroll">
                        <template x-for="r in filteredRooms" :key="r.id">
                            <div @click="selectRoom(r.id)"
                                 :class="selectedRoomId == r.id ? 'border-emerald-500/50 bg-emerald-500/10 shadow-md ring-1 ring-emerald-500/30' : 'border-white/[0.06] bg-[#0F1420] hover:border-white/20 hover:bg-white/[0.03]'"
                                 class="p-3.5 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-white" x-text="'Room ' + r.room_number"></span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider"
                                              :class="r.block_gender === 'female' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20'"
                                              x-text="r.block_gender + ' block'"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5" x-text="r.block_code + ' - ' + r.block_name"></div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <span class="text-xs font-semibold text-emerald-400 block" x-text="(r.available_beds ?? r.capacity) + ' beds free'"></span>
                                        <span class="text-[10px] text-slate-400" x-text="'Capacity: ' + r.capacity"></span>
                                    </div>
                                    <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors"
                                         :class="selectedRoomId == r.id ? 'border-emerald-500 bg-emerald-500 text-slate-950 font-bold' : 'border-white/20 text-transparent'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="filteredRooms.length === 0" class="p-8 text-center text-slate-400 text-xs">
                            No available rooms match your filter criteria.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Compatibility Status Banner -->
            <div x-show="selectedStudents.length > 0 && selectedRoom">
                <template x-if="!isGenderMismatch && !hasMixedGenders">
                    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center gap-3 text-xs text-emerald-400">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="font-semibold">Verified Gender Match:</strong>
                            <span x-text="selectedStudents.length + ' ' + selectedStudents[0].gender + ' student(s)'"></span> &rarr; <span x-text="selectedRoom.block_code + ' Room ' + selectedRoom.room_number + ' (' + selectedRoom.block_gender + ' Block)'"></span>. Ready for batch allocation.
                        </div>
                    </div>
                </template>

                <template x-if="isGenderMismatch">
                    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center gap-3 text-xs text-rose-300">
                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <strong class="font-semibold">Strict Accommodation Violation:</strong> Selected student gender does not match the <span class="capitalize font-bold" x-text="selectedRoom.block_gender"></span> block. Please select compatible students or room.
                        </div>
                    </div>
                </template>

                <template x-if="hasMixedGenders">
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center gap-3 text-xs text-amber-300">
                        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <strong class="font-semibold">Mixed Gender Batch:</strong> Selected students include both male and female. All students assigned together must have the same gender.
                        </div>
                    </div>
                </template>
            </div>

            <!-- Optional Remarks -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 space-y-4">
                <div>
                    <label for="remarks" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Placement Remarks / Special Needs (Optional)
                    </label>
                    <input type="text" name="remarks" id="remarks" value="{{ old('remarks') }}"
                           placeholder="e.g. Ground floor assignment, medical accommodation request, first-year cohort"
                           class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('proctor.dashboard') }}" class="px-4 py-2.5 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            :disabled="selectedStudentIds.length === 0 || !selectedRoomId || isGenderMismatch || hasMixedGenders"
                            :class="(selectedStudentIds.length === 0 || !selectedRoomId || isGenderMismatch || hasMixedGenders) ? 'opacity-40 cursor-not-allowed bg-white/[0.05] text-slate-500' : 'bg-emerald-500 text-slate-950 hover:bg-emerald-400 font-semibold shadow-lg shadow-emerald-500/20 active:scale-[0.98]'"
                            class="px-6 py-2.5 text-xs rounded-xl transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="selectedStudentIds.length > 1 ? 'Allocate ' + selectedStudentIds.length + ' Students to Room' : 'Confirm Student Allocation'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>