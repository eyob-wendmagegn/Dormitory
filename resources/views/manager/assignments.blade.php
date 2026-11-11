<x-app-layout title="Proctor Assignments">
    <div class="space-y-6" x-data="{
        searchQuery: '',
        newDepts: [],
        newYears: [],
        deptFilter: '',
        deptDropdownOpen: false,
        editModalOpen: false,
        editDeptDropdownOpen: false,
        editAssignment: {
            id: null,
            block_id: '',
            proctor_id: '',
            assigned_departments: [],
            assigned_years: [],
            block_name: '',
            proctor_name: ''
        },
        editDeptFilter: '',
        openEdit(asg) {
            this.editAssignment = {
                id: asg.id,
                block_id: asg.block_id,
                proctor_id: asg.proctor_id,
                assigned_departments: Array.isArray(asg.assigned_departments) ? [...asg.assigned_departments] : [],
                assigned_years: Array.isArray(asg.assigned_years) ? asg.assigned_years.map(y => parseInt(y)) : [],
                block_name: asg.block ? (asg.block.block_code + ' - ' + asg.block.name) : '',
                proctor_name: asg.proctor ? asg.proctor.name : ''
            };
            this.editDeptDropdownOpen = false;
            this.editModalOpen = true;
        },
        toggleDept(dept, targetArray) {
            const index = targetArray.indexOf(dept);
            if (index > -1) {
                targetArray.splice(index, 1);
            } else if (targetArray.length < 10) {
                targetArray.push(dept);
            }
        },
        toggleYear(year, targetArray) {
            year = parseInt(year);
            const index = targetArray.indexOf(year);
            if (index > -1) {
                targetArray.splice(index, 1);
            } else if (targetArray.length < 5) {
                targetArray.push(year);
            }
        }
    }">
        <!-- Policy & Guidance Notice Banner -->
        <div class="p-5 rounded-2xl bg-[#121927]/80 backdrop-blur-md border-l-4 border-l-emerald-400 border border-white/[0.07] space-y-1.5 shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h4 class="text-xs font-semibold text-white tracking-wide uppercase">Proctor Assignment & Academic Scope Policy</h4>
            </div>
            <p class="text-xs text-slate-300 pl-7 leading-relaxed">
                <strong class="text-white">Strict 1-to-1 Block Policy:</strong> Each dormitory block has exactly <span class="text-emerald-400 font-semibold">one proctor</span>, and a proctor is tied to <span class="text-emerald-400 font-semibold">one block</span>.
                You can assign each proctor <span class="text-emerald-400 font-semibold">at most 10 departments</span> and <span class="text-emerald-400 font-semibold">at most 5 batches (1st - 7th Year)</span>.
            </p>
            <p class="text-xs text-slate-400 pl-7 leading-relaxed">
                <strong class="text-cyan-400">Dual-Gender Department Sharing:</strong> Departments and batches can be assigned to multiple proctors. Male and female students of the same department/year are placed into their respective gender blocks (male students to male proctor block, female students to female proctor block).
            </p>
        </div>

        <!-- Assignment Form Console -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25 space-y-6">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Staff Allocation Console</span>
                </div>
                <h2 class="text-xl font-bold text-white tracking-tight">Assign Proctor to Dormitory Block & Academic Scope</h2>
                <p class="text-xs text-slate-400 mt-0.5">Select a block, proctor, and assign up to 10 departments and up to 5 batches (years).</p>
            </div>

            <form method="POST" action="{{ route('manager.assignments.store') }}" class="space-y-6">
                @csrf

                <!-- Block & Proctor Selection -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Dormitory Block *</label>
                        <select name="block_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                            <option value="">-- Select Available Block --</option>
                            @foreach($blocks as $b)
                                @php
                                    $isBlockAssigned = $b->activeProctorAssignment !== null;
                                @endphp
                                <option value="{{ $b->id }}" {{ $isBlockAssigned ? 'disabled class=text-slate-500' : '' }}>
                                    {{ $b->block_code }} - {{ $b->name }} ({{ ucfirst($b->gender_category) }} Block)
                                    @if($isBlockAssigned)
                                        [Assigned: {{ $b->activeProctorAssignment->proctor->name }}]
                                    @else
                                        [Available]
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Assigned Proctor *</label>
                        <select name="proctor_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                            <option value="">-- Select Available Proctor --</option>
                            @foreach($proctors as $p)
                                @php
                                    $activeAssignment = $p->activeProctorAssignments->first();
                                @endphp
                                <option value="{{ $p->id }}" {{ $activeAssignment ? 'disabled class=text-slate-500' : '' }}>
                                    {{ $p->name }} ({{ $p->university_id }} &bull; {{ ucfirst($p->gender ?? 'staff') }})
                                    @if($activeAssignment)
                                        [Assigned: {{ $activeAssignment->block->block_code }}]
                                    @else
                                        [Available]
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Academic Scope: SCROLLABLE DROPDOWN DEPARTMENTS SELECTOR -->
                <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.06] space-y-3 relative" @click.outside="deptDropdownOpen = false">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold uppercase text-white tracking-wider flex items-center gap-2">
                                <span>Assign Departments</span>
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full font-mono"
                                      :class="newDepts.length === 10 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'"
                                      x-text="newDepts.length + ' / 10 max selected'"></span>
                            </span>
                            <p class="text-xs text-slate-400 mt-0.5">Choose up to 10 departments from the scrollable dropdown list below.</p>
                        </div>

                        <button type="button" @click="deptDropdownOpen = !deptDropdownOpen"
                                class="px-3.5 py-1.5 text-xs font-medium rounded-xl border border-white/10 bg-white/[0.05] text-emerald-400 hover:text-emerald-300 hover:bg-white/[0.09] flex items-center gap-1.5 shadow-sm transition-all">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="deptDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <span x-text="deptDropdownOpen ? 'Close Dropdown' : 'Select Departments'"></span>
                        </button>
                    </div>

                    <!-- Hidden inputs for form submission -->
                    <template x-for="dept in newDepts" :key="dept">
                        <input type="hidden" name="assigned_departments[]" :value="dept" />
                    </template>

                    <!-- Selected Departments Chips Container -->
                    <div class="min-h-[44px] p-2.5 rounded-xl bg-[#0B0F17] border border-white/[0.08] flex flex-wrap items-center gap-1.5 cursor-pointer"
                         @click="deptDropdownOpen = true">
                        <template x-if="newDepts.length === 0">
                            <span class="text-xs text-slate-500 italic flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                <span>Click here to open scrollable list of departments (max 10)...</span>
                            </span>
                        </template>

                        <template x-for="dept in newDepts" :key="dept">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium shadow-sm">
                                <span x-text="dept"></span>
                                <button type="button" @click.stop="toggleDept(dept, newDepts)" class="text-emerald-400 hover:text-white font-bold leading-none">&times;</button>
                            </span>
                        </template>
                    </div>

                    <!-- Floating / Expandable Scrollable Dropdown Menu -->
                    <div x-show="deptDropdownOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="p-4 bg-[#121927] border border-white/10 rounded-2xl shadow-2xl space-y-3 z-30">
                        <div class="flex items-center justify-between pb-2 border-b border-white/[0.06]">
                            <span class="text-[11px] font-semibold uppercase text-white tracking-wider">Search & Select Departments</span>
                            <span class="text-[10px] text-slate-400" x-text="newDepts.length + ' / 10 selected'"></span>
                        </div>

                        <!-- Dropdown Search Bar -->
                        <div class="relative">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="deptFilter" placeholder="Filter departments (e.g. Computer Science, Accounting, Surveying, Civil...)"
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white placeholder-slate-500" />
                        </div>

                        <!-- Scrollable Division & Department List -->
                        <div class="max-h-72 overflow-y-auto pr-1 space-y-3 custom-scroll">
                            @foreach($divisions as $college => $depts)
                                <div class="bg-[#0B0F17]/80 rounded-xl p-3 border border-white/[0.04] space-y-1.5">
                                    <span class="block text-[10px] uppercase font-semibold text-emerald-400 tracking-wider px-1 pb-1">
                                        {{ $college }}
                                    </span>
                                    <div class="space-y-1">
                                        @foreach($depts as $dept)
                                            <div x-show="!deptFilter || '{{ strtolower($dept) }}'.includes(deptFilter.toLowerCase())"
                                                 @click="toggleDept('{{ $dept }}', newDepts)"
                                                 :class="newDepts.includes('{{ $dept }}')
                                                     ? 'bg-emerald-500/15 text-emerald-400 font-semibold border-emerald-500/30'
                                                     : (newDepts.length >= 10
                                                         ? 'opacity-40 cursor-not-allowed text-slate-500 border-transparent'
                                                         : 'hover:bg-white/[0.04] text-slate-300 cursor-pointer border-transparent')"
                                                 class="flex items-center justify-between px-3 py-1.5 rounded-lg border text-xs transition-colors select-none">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-4 h-4 rounded border flex items-center justify-center transition-colors"
                                                         :class="newDepts.includes('{{ $dept }}') ? 'border-emerald-500 bg-emerald-500 text-slate-950' : 'border-white/20 bg-white/[0.02] text-transparent'">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    </div>
                                                    <span>{{ $dept }}</span>
                                                </div>
                                                <span class="text-[10px] text-slate-500" x-show="newDepts.includes('{{ $dept }}')">Selected</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Academic Scope: Batches (At most 5) -->
                <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.06] space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold uppercase text-white tracking-wider flex items-center gap-2">
                                <span>Assign Batches / Academic Years</span>
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full font-mono"
                                      :class="newYears.length === 5 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'"
                                      x-text="newYears.length + ' / 5 max selected'"></span>
                            </span>
                            <p class="text-xs text-slate-400 mt-0.5">Select up to 5 batches from 1st Year up to 7th Year for this proctor.</p>
                        </div>
                    </div>

                    <!-- Hidden inputs for form submission -->
                    <template x-for="yr in newYears" :key="yr">
                        <input type="hidden" name="assigned_years[]" :value="yr" />
                    </template>

                    <!-- Batches Pills Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2">
                        @foreach($allBatches as $yrNum => $yrLabel)
                            <div @click="toggleYear({{ $yrNum }}, newYears)"
                                 :class="newYears.includes({{ $yrNum }})
                                     ? 'bg-emerald-500/15 border-emerald-500 text-emerald-400 font-semibold ring-1 ring-emerald-500/30'
                                     : (newYears.length >= 5
                                         ? 'opacity-40 cursor-not-allowed bg-[#0F1420] border-white/[0.06] text-slate-500'
                                         : 'bg-[#0F1420] border-white/[0.08] text-slate-400 hover:text-white hover:border-white/20 cursor-pointer')"
                                 class="p-2.5 rounded-xl border text-xs text-center transition-all flex items-center justify-center gap-1.5 select-none">
                                <span>{{ $yrLabel }}</span>
                                <template x-if="newYears.includes({{ $yrNum }})">
                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </template>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="submit" class="py-2.5 px-6 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Confirm Proctor & Scope Assignment</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Assignments Table with Search, Scope, Edit, and Delete -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-5 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Active Proctor Assignments & Scopes</h3>
                    <p class="text-xs text-slate-400">Each active block is tied to exactly one block proctor with assigned departments and batches.</p>
                </div>
                <div class="w-full sm:w-64">
                    <input type="text" x-model="searchQuery" placeholder="Filter block, proctor, or department..." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500" />
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Block</th>
                            <th class="px-5 py-3.5">Proctor</th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Assigned Departments (Max 10)</th>
                            <th class="px-5 py-3.5">Assigned Batches (Max 5)</th>
                            <th class="px-5 py-3.5">Assigned Date</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($assignments as $asg)
                            @php
                                $depts = $asg->assigned_departments_list;
                                $deptCount = count($depts);
                                $years = $asg->assigned_years_list;
                                $yearCount = count($years);
                                $searchHaystack = strtolower(
                                    $asg->block->block_code . ' ' .
                                    $asg->block->name . ' ' .
                                    $asg->proctor->name . ' ' .
                                    implode(' ', $depts)
                                );
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors"
                                x-show="!searchQuery || '{{ $searchHaystack }}'.includes(searchQuery.toLowerCase())">
                                <td class="px-5 py-3.5 font-bold text-white">
                                    <span class="text-emerald-400 font-mono">{{ $asg->block->block_code }}</span> - {{ $asg->block->name }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-white block">{{ $asg->proctor->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $asg->proctor->university_id }}</span>
                                </td>
                                <td class="px-5 py-3.5 capitalize">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium {{ $asg->block->gender_category === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $asg->block->gender_category }} Block
                                    </span>
                                </td>
                                <!-- Assigned Departments -->
                                <td class="px-5 py-3.5">
                                    @if($deptCount > 0)
                                        <div class="flex flex-wrap items-center gap-1 max-w-xs">
                                            @foreach(array_slice($depts, 0, 3) as $d)
                                                <span class="px-2 py-0.5 rounded-md bg-white/[0.04] text-slate-300 border border-white/10 text-[10px]">
                                                    {{ $d }}
                                                </span>
                                            @endforeach
                                            @if($deptCount > 3)
                                                <span class="px-1.5 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-medium" title="{{ implode(', ', array_slice($depts, 3)) }}">
                                                    +{{ $deptCount - 3 }} more
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-500 italic">All Departments</span>
                                    @endif
                                </td>
                                <!-- Assigned Batches -->
                                <td class="px-5 py-3.5">
                                    @if($yearCount > 0)
                                        <div class="flex flex-wrap items-center gap-1">
                                            @foreach($years as $y)
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-medium">
                                                    Yr {{ $y }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-500 italic">All Batches</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-[11px] text-slate-400">{{ $asg->assigned_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit Assignment Button -->
                                        <button type="button" @click="openEdit({{ Js::from($asg) }})" title="Edit Assignment & Scope" class="p-1.5 text-slate-400 hover:text-white hover:bg-white/[0.06] rounded-lg border border-white/10 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <!-- End Assignment Button -->
                                        <form method="POST" action="{{ route('manager.assignments.end', $asg) }}" class="inline" onsubmit="return confirm('End proctor assignment for this block?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-[11px] font-medium text-amber-400 hover:text-amber-300 bg-amber-500/10 border border-amber-500/20 rounded-lg transition-colors">
                                                End
                                            </button>
                                        </form>

                                        <!-- Delete Assignment Button -->
                                        <form method="POST" action="{{ route('manager.assignments.destroy', $asg) }}" class="inline" onsubmit="return confirm('Permanently delete assignment of {{ addslashes($asg->proctor->name) }} to {{ $asg->block->block_code }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Assignment" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg border border-rose-500/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">No active proctor assignments.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: Edit Proctor Assignment & Scope     -->
        <!-- ========================================== -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="editModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/10 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                        <div>
                            <h3 class="text-base font-semibold text-white tracking-tight">Edit Proctor Assignment & Academic Scope</h3>
                            <p class="text-xs text-slate-400" x-text="'Updating assignment #' + editAssignment.id"></p>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'/proctor-manager/assignments/' + editAssignment.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Dormitory Block</label>
                                <select name="block_id" x-model="editAssignment.block_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                                    @foreach($blocks as $b)
                                        <option value="{{ $b->id }}">
                                            {{ $b->block_code }} - {{ $b->name }} ({{ ucfirst($b->gender_category) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Assigned Proctor</label>
                                <select name="proctor_id" x-model="editAssignment.proctor_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                                    @foreach($proctors as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->name }} ({{ $p->university_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Edit Departments (At most 10) - Scrollable Dropdown -->
                        <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06] space-y-2 relative" @click.outside="editDeptDropdownOpen = false">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-medium text-white flex items-center gap-2">
                                    <span>Assigned Departments</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                          x-text="editAssignment.assigned_departments.length + ' / 10 max'"></span>
                                </label>
                                <button type="button" @click="editDeptDropdownOpen = !editDeptDropdownOpen"
                                        class="px-2.5 py-1 text-[11px] font-medium text-emerald-400 hover:text-emerald-300 bg-white/[0.05] border border-white/10 rounded-lg transition-colors">
                                    <span x-text="editDeptDropdownOpen ? 'Close List' : 'Select From Dropdown'"></span>
                                </button>
                            </div>

                            <template x-for="dept in editAssignment.assigned_departments" :key="dept">
                                <input type="hidden" name="assigned_departments[]" :value="dept" />
                            </template>

                            <!-- Selected chips in modal -->
                            <div class="min-h-[38px] p-2 rounded-xl bg-[#0B0F17] border border-white/[0.08] flex flex-wrap items-center gap-1 cursor-pointer"
                                 @click="editDeptDropdownOpen = true">
                                <template x-if="editAssignment.assigned_departments.length === 0">
                                    <span class="text-[11px] text-slate-500 italic">Click to select departments from scrollable dropdown...</span>
                                </template>
                                <template x-for="dept in editAssignment.assigned_departments" :key="dept">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[11px]">
                                        <span x-text="dept"></span>
                                        <button type="button" @click.stop="toggleDept(dept, editAssignment.assigned_departments)" class="font-bold text-emerald-400 hover:text-white">&times;</button>
                                    </span>
                                </template>
                            </div>

                            <!-- Floating Scrollable Menu in Edit Modal -->
                            <div x-show="editDeptDropdownOpen" class="p-3 bg-[#121927] border border-white/10 rounded-2xl shadow-xl space-y-2 mt-1">
                                <input type="text" x-model="editDeptFilter" placeholder="Filter departments..." class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-1.5 text-xs text-white w-full" />
                                <div class="max-h-56 overflow-y-auto pr-1 space-y-2 custom-scroll">
                                    @foreach($divisions as $college => $depts)
                                        <div class="bg-[#0B0F17]/80 p-2.5 rounded-xl border border-white/[0.04]">
                                            <span class="block text-[10px] uppercase font-semibold text-emerald-400 mb-1">{{ $college }}</span>
                                            <div class="space-y-0.5">
                                                @foreach($depts as $dept)
                                                    <div x-show="!editDeptFilter || '{{ strtolower($dept) }}'.includes(editDeptFilter.toLowerCase())"
                                                         @click="toggleDept('{{ $dept }}', editAssignment.assigned_departments)"
                                                         :class="editAssignment.assigned_departments.includes('{{ $dept }}')
                                                             ? 'bg-emerald-500/15 text-emerald-400 font-semibold'
                                                             : (editAssignment.assigned_departments.length >= 10
                                                                 ? 'opacity-40 cursor-not-allowed text-slate-500'
                                                                 : 'hover:bg-white/[0.04] text-slate-300 cursor-pointer')"
                                                         class="flex items-center justify-between px-2.5 py-1 rounded text-[11px] transition-colors select-none">
                                                        <span>{{ $dept }}</span>
                                                        <template x-if="editAssignment.assigned_departments.includes('{{ $dept }}')">
                                                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </template>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Edit Batches (At most 5) -->
                        <div class="p-3.5 rounded-2xl bg-[#0F1420] border border-white/[0.06] space-y-2">
                            <label class="block text-[11px] font-medium text-white flex items-center gap-2">
                                <span>Assigned Batches (Years)</span>
                                <span class="text-[9px] px-2 py-0.5 rounded-full font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                      x-text="editAssignment.assigned_years.length + ' / 5 max'"></span>
                            </label>

                            <template x-for="yr in editAssignment.assigned_years" :key="yr">
                                <input type="hidden" name="assigned_years[]" :value="yr" />
                            </template>

                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-1.5">
                                @foreach($allBatches as $yrNum => $yrLabel)
                                    <div @click="toggleYear({{ $yrNum }}, editAssignment.assigned_years)"
                                         :class="editAssignment.assigned_years.includes({{ $yrNum }})
                                             ? 'bg-emerald-500/15 border-emerald-500 text-emerald-400 font-semibold'
                                             : (editAssignment.assigned_years.length >= 5
                                                 ? 'opacity-40 cursor-not-allowed bg-[#0B0F17] border-white/[0.04] text-slate-500'
                                                 : 'bg-[#0B0F17] border-white/[0.08] text-slate-400 hover:text-white cursor-pointer')"
                                         class="p-2 rounded-xl border text-[11px] text-center transition-all select-none">
                                        <span>{{ $yrLabel }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                                Save Scope & Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>