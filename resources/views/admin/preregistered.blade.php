<x-app-layout title="Pre-Registered Users">
    <div class="space-y-6" x-data="{ importModalOpen: false }">
        <!-- Top bar with Actions -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90 mb-1 block">Roster Management</span>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Pre-Registered Users & Registrar Rosters</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">Create individual pre-authorizations or bulk-import official student rosters from CSV / Excel.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.users.sample_csv') }}" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download CSV Template</span>
                </a>
                <button type="button" @click="importModalOpen = true" class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2 active:scale-[0.98]">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>+ Import CSV / Excel</span>
                </button>
            </div>
        </div>

        <!-- Pre-Register Creation Form -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Pre-Register Single Authorized Account</h3>
                    <p class="text-xs text-slate-400">Administrator pre-authorizes user profile. The user activates it with their Name and University ID.</p>
                </div>
                <span class="text-xs font-medium px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                    Administrator Security Role
                </span>
            </div>

            <form method="POST" action="{{ route('admin.users.preregister') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @csrf
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Bethlehem Assefa" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">University ID</label>
                    <input type="text" name="university_id" required placeholder="e.g. WLD/0301/16" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono placeholder-slate-500 transition-all" />
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Assigned Role</label>
                    <select name="role" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                        <option value="student">Student</option>
                        <option value="proctor">Proctor</option>
                        <option value="proctor_manager">Proctor Manager</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Gender</label>
                    <select name="gender" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Department</label>
                    <input type="text" name="department" list="pre-dept-options" placeholder="e.g. Computer Science" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                    <datalist id="pre-dept-options">
                        <option value="Computer Science">
                        <option value="Software Engineering">
                        <option value="Information Technology">
                        <option value="Electrical & Computer Engineering">
                        <option value="Mechanical Engineering">
                        <option value="Civil Engineering">
                        <option value="Chemical Engineering">
                        <option value="Medicine">
                        <option value="Pharmacy">
                        <option value="Nursing">
                        <option value="Accounting & Finance">
                        <option value="Economics">
                        <option value="Management">
                        <option value="Law">
                        <option value="Architecture">
                    </datalist>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Academic Year</label>
                    <select name="academic_year" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                        <option value="">None (Staff)</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                        <option value="5">5th Year</option>
                        <option value="6">6th Year</option>
                        <option value="7">7th Year</option>
                    </select>
                </div>
                <div class="sm:col-span-2 md:col-span-3 lg:col-span-6 flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98]">
                        + Save Pre-Registered Record
                    </button>
                </div>
            </form>
        </div>

        <!-- Filter Bar -->
        <div class="p-4 rounded-2xl bg-[#111726]/70 border border-white/[0.06] backdrop-blur-md">
            <form method="GET" action="{{ route('admin.preregister.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, ID or department..." class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />

                <select name="department" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>

                <select name="role" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Roles</option>
                    <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
                    <option value="proctor" {{ request('role') === 'proctor' ? 'selected' : '' }}>Proctor</option>
                    <option value="proctor_manager" {{ request('role') === 'proctor_manager' ? 'selected' : '' }}>Proctor Manager</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>

                <select name="gender" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Genders</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                </select>

                <select name="year" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Academic Years</option>
                    @foreach([1, 2, 3, 4, 5, 6, 7] as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}th Year</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-md shadow-emerald-500/20 active:scale-[0.98]">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'department', 'role', 'gender', 'year']))
                        <a href="{{ route('admin.preregister.index') }}" class="px-3.5 py-2.5 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] border border-white/10 rounded-xl transition-all flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Pending Pre-registered Accounts Table -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Pending / Unclaimed Pre-Registrations</h3>
                    <p class="text-xs text-slate-400">Users who have not yet submitted their registration claim</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.03] text-slate-400 uppercase text-[10px] font-semibold border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3">
                                <div class="inline-flex items-center">
                                    <span>Full Name</span>
                                    <x-sort-dropdown column="name" label="Full Name" asc-label="Ascending (A → Z)" desc-label="Descending (Z → A)" />
                                </div>
                            </th>
                            <th class="px-5 py-3">
                                <div class="inline-flex items-center">
                                    <span>University ID</span>
                                    <x-sort-dropdown column="university_id" label="University ID" asc-label="Ascending (Low → High)" desc-label="Descending (High → Low)" />
                                </div>
                            </th>
                            <th class="px-5 py-3">Intended Role</th>
                            <th class="px-5 py-3">Gender</th>
                            <th class="px-5 py-3">Department</th>
                            <th class="px-5 py-3">Academic Year</th>
                            <th class="px-5 py-3">Claim Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($pendingUsers as $pu)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 font-medium text-white">{{ $pu->name }}</td>
                                <td class="px-5 py-3.5 font-semibold text-emerald-400 font-mono">{{ $pu->university_id }}</td>
                                <td class="px-5 py-3.5 capitalize text-slate-300">{{ str_replace('_', ' ', $pu->role) }}</td>
                                <td class="px-5 py-3.5 capitalize text-slate-300">{{ $pu->gender ?? 'N/A' }}</td>
                                <td class="px-5 py-3.5 text-white">{{ $pu->department ?? 'General' }}</td>
                                <td class="px-5 py-3.5 text-slate-300">{{ $pu->academic_year_label ?? '-' }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Pending Claim
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">No pending pre-registered users found matching criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-white/[0.06]">
                {{ $pendingUsers->links() }}
            </div>
        </div>

        <!-- Bulk Import Modal -->
        <div x-show="importModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            <div class="bg-[#121927] border border-white/[0.08] w-full max-w-2xl rounded-3xl p-6 sm:p-7 shadow-2xl relative"
                 @click.away="importModalOpen = false">
                <div class="flex items-start justify-between pb-4 border-b border-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white tracking-tight">Bulk Import Students (CSV / Excel Sheet)</h3>
                            <p class="text-xs text-slate-400">Import thousands of students from Registrar rosters with optional range & filter slicing.</p>
                        </div>
                    </div>
                    <button type="button" @click="importModalOpen = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf

                    <!-- File Picker -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[11px] uppercase font-semibold text-emerald-400 tracking-wider">Choose CSV / Excel File <span class="text-rose-400">*</span></label>
                            <a href="{{ route('admin.users.sample_csv') }}" class="text-[11px] text-emerald-400 hover:underline flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Download Sample CSV</span>
                            </a>
                        </div>
                        <input type="file" name="file" accept=".csv, .txt" required
                               class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white/[0.06] file:text-white hover:file:bg-white/[0.1] transition-all" />
                        <p class="text-[10px] text-slate-400 mt-1">Columns supported: <code>name</code>, <code>university_id</code>, <code>gender</code>, <code>department</code>, <code>academic_year</code>, <code>role</code></p>
                    </div>

                    <!-- Range & Filter Controls (Optional Subsets) -->
                    <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05] space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="block text-[11px] font-semibold uppercase text-white tracking-wider">Import Filters & Target Selection (Optional)</span>
                            <span class="text-[10px] text-emerald-400 font-mono">Single ID &bull; Ranges &bull; Department</span>
                        </div>
                        <p class="text-[10px] text-slate-400">Import all roster records or specify a single user ID, range, department, or batch.</p>

                        <!-- Single User ID Import Highlight Box -->
                        <div class="p-3.5 rounded-xl bg-[#0F1420]/80 border border-emerald-500/20 space-y-1.5">
                            <label class="block text-[11px] uppercase font-semibold text-emerald-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Import Single User by ID</span>
                            </label>
                            <input type="text" name="single_id" placeholder="e.g. 3001 or WLD/0301/16 (Extracts only this user)" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500" />
                            <span class="text-[9px] text-slate-400 block">If provided, only this specific student will be imported from the CSV file.</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Import by ID Range or Single ID -->
                            <div>
                                <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Import by ID Range / List</label>
                                <input type="text" name="id_range" placeholder="e.g. 3000-4000 or WLD/1000-WLD/2000" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white" />
                                <span class="text-[9px] text-slate-500">Rows with ID in this range or single ID</span>
                            </div>

                            <!-- Import by Row Number Range -->
                            <div>
                                <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Import by Row Number Range</label>
                                <input type="text" name="row_range" placeholder="e.g. 1500-2000 or 1-500" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white" />
                                <span class="text-[9px] text-slate-500">Slice file rows (e.g. rows 1500 to 2000)</span>
                            </div>

                            <!-- Import by Department -->
                            <div>
                                <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Import by Department</label>
                                <input type="text" name="department" list="import-dept-options" placeholder="-- All or e.g. Computer Science --" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white" />
                                <datalist id="import-dept-options">
                                    <option value="Computer Science">
                                    <option value="Software Engineering">
                                    <option value="Information Technology">
                                    <option value="Electrical Engineering">
                                    <option value="Mechanical Engineering">
                                    <option value="Civil Engineering">
                                    <option value="Medicine">
                                    <option value="Pharmacy">
                                    <option value="Accounting & Finance">
                                    <option value="Economics">
                                </datalist>
                                <span class="text-[9px] text-slate-500">Only import rows matching this department</span>
                            </div>

                            <!-- Import by Academic Year -->
                            <div>
                                <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Import by Academic Year</label>
                                <select name="academic_year" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white">
                                    <option value="">-- All Academic Years in File --</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                    <option value="5">5th Year</option>
                                    <option value="6">6th Year</option>
                                    <option value="7">7th Year</option>
                                </select>
                                <span class="text-[9px] text-slate-500">Only import rows matching this year</span>
                            </div>
                        </div>

                        <!-- Duplicate Handling Mode -->
                        <div class="pt-2 border-t border-white/[0.06] flex items-center justify-between">
                            <div>
                                <span class="block text-[11px] font-semibold text-white">Duplicate University ID Handling</span>
                                <span class="text-[9px] text-slate-400">What to do if a student ID already exists in the system</span>
                            </div>
                            <select name="duplicate_handling" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-1.5 text-xs text-white">
                                <option value="skip">Skip Existing (Safe)</option>
                                <option value="update">Update Profile Info</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-white/[0.06]">
                        <button type="button" @click="importModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2 active:scale-[0.98]">
                            <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Start Bulk Import</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
