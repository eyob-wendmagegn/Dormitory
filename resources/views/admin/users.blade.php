<x-app-layout title="User Management">
    <div class="space-y-6" x-data="{
        editModalOpen: false,
        bulkModalOpen: false,
        importModalOpen: false,
        editUser: {
            id: null,
            name: '',
            university_id: '',
            email: '',
            role: 'student',
            gender: 'male',
            department: '',
            academic_year: '1',
            registration_status: 'completed',
            is_active: true
        },
        selectedUsers: [],
        selectAll: false,
        toggleAll(event) {
            if (event.target.checked) {
                const checkboxes = document.querySelectorAll('.user-checkbox:not(:disabled)');
                this.selectedUsers = Array.from(checkboxes).map(cb => cb.value);
            } else {
                this.selectedUsers = [];
            }
        },
        openEdit(user) {
            this.editUser = {
                id: user.id,
                name: user.name,
                university_id: user.university_id,
                email: user.email || '',
                role: user.role,
                gender: user.gender || '',
                department: user.department || '',
                academic_year: user.academic_year || 1,
                registration_status: user.registration_status || 'completed',
                is_active: user.is_active !== false
            };
            this.editModalOpen = true;
        }
    }">
        <!-- Top bar with Actions -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-emerald-400/90 mb-1 block">Directory Console</span>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Institutional User Directory</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">Manage administrators, proctor managers, block proctors, and student residents.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Delete Selected Button (Active when checkboxes selected) -->
                <form method="POST" action="{{ route('admin.users.bulk_destroy') }}" x-show="selectedUsers.length > 0" x-cloak onsubmit="return confirm('Permanently delete all ' + selectedUsers.length + ' selected users?');">
                    @csrf
                    <template x-for="uid in selectedUsers" :key="uid">
                        <input type="hidden" name="user_ids[]" :value="uid" />
                    </template>
                    <button type="submit" class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 rounded-xl transition-all flex items-center gap-2 shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Delete Selected (<span x-text="selectedUsers.length"></span>)</span>
                    </button>
                </form>

                <!-- Bulk Delete by ID Range Trigger -->
                <button type="button" @click="bulkModalOpen = true" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-rose-400 hover:text-white bg-white/[0.05] hover:bg-rose-500/20 border border-rose-500/30 rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Bulk Delete (Range)</span>
                </button>

                <!-- Bulk Import CSV Button -->
                <button type="button" @click="importModalOpen = true" class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2 active:scale-[0.98]">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>+ Import CSV / Excel</span>
                </button>

                <!-- Pre-Register User Link -->
                <a href="{{ route('admin.preregister.index') }}" class="px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>+ Pre-Register User</span>
                </a>
            </div>
        </div>

        <!-- Filters Form -->
        <div class="p-4 rounded-2xl bg-[#111726]/70 border border-white/[0.06] backdrop-blur-md">
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-7 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, ID or email..." class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                
                <select name="department" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>

                <select name="role" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="proctor_manager" {{ request('role') === 'proctor_manager' ? 'selected' : '' }}>Proctor Manager</option>
                    <option value="proctor" {{ request('role') === 'proctor' ? 'selected' : '' }}>Proctor</option>
                    <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
                </select>

                <select name="gender" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Genders</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                </select>

                <select name="status" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Claimed)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Unclaimed)</option>
                </select>

                <button type="submit" class="px-4 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-md shadow-emerald-500/20 active:scale-[0.98]">
                    Filter Directory
                </button>

                @if(request()->hasAny(['search', 'department', 'role', 'gender', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2.5 text-xs font-medium text-center text-slate-400 hover:text-white bg-white/[0.05] border border-white/10 rounded-xl transition-all flex items-center justify-center">
                        Reset Filters
                    </a>
                @endif
            </form>
        </div>

        <!-- Users Table -->
        <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-white/[0.03] text-slate-400 uppercase text-[10px] font-semibold border-b border-white/[0.06]">
                        <tr>
                            <th class="px-4 py-3.5 w-10 text-center">
                                <input type="checkbox" @change="toggleAll($event)" class="rounded bg-[#0F1420] border-white/20 text-emerald-500 focus:ring-emerald-500" />
                            </th>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>User</span>
                                    <x-sort-dropdown column="name" label="User Name" asc-label="Ascending (A → Z)" desc-label="Descending (Z → A)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>University ID</span>
                                    <x-sort-dropdown column="university_id" label="University ID" asc-label="Ascending (Low → High)" desc-label="Descending (High → Low)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">Role</th>
                            <th class="px-5 py-3.5">Gender</th>
                            <th class="px-5 py-3.5">Department</th>
                            <th class="px-5 py-3.5">Account Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($users as $u)
                            <tr class="hover:bg-white/[0.02] transition-colors {{ ($u->is_active === false) ? 'opacity-70 bg-rose-950/10' : '' }}">
                                <td class="px-4 py-3.5 text-center">
                                    @if($u->id !== auth()->id())
                                        <input type="checkbox" value="{{ $u->id }}" x-model="selectedUsers" class="user-checkbox rounded bg-[#0F1420] border-white/20 text-emerald-500 focus:ring-emerald-500" />
                                    @else
                                        <span class="text-[10px] text-slate-500">&bull;</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="w-8 h-8 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <span class="font-medium text-white block">{{ $u->name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $u->email ?? 'Pre-registered (No Email)' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-emerald-400 font-mono">{{ $u->university_id }}</td>
                                <td class="px-5 py-3.5 capitalize text-slate-300">{{ str_replace('_', ' ', $u->role) }}</td>
                                <td class="px-5 py-3.5">
                                    @if($u->gender)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium capitalize {{ $u->gender === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                            {{ $u->gender }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">N/A</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-200">
                                    {{ $u->department ?? 'General' }}
                                    @if($u->academic_year && $u->isStudent())
                                        <span class="text-[10px] text-slate-400 block">Year {{ $u->academic_year }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <!-- Active / Deactivated indicator -->
                                        @if($u->is_active !== false)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                Deactivated
                                            </span>
                                        @endif

                                        <span class="text-[10px] text-slate-400 capitalize">
                                            ({{ $u->registration_status }})
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Activate / Deactivate Toggle Button -->
                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.status', $u) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" title="{{ $u->is_active !== false ? 'Deactivate this user' : 'Activate this user' }}" class="px-2.5 py-1 text-[11px] font-medium rounded-lg border transition-colors {{ $u->is_active !== false ? 'text-amber-400 hover:text-amber-300 border-amber-500/20 bg-amber-500/10 hover:bg-amber-500/20' : 'text-emerald-400 hover:text-emerald-300 border-emerald-500/20 bg-emerald-500/10 hover:bg-emerald-500/20' }}">
                                                    {{ $u->is_active !== false ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Edit Icon Button -->
                                        <button type="button" @click="openEdit({{ Js::from($u) }})" title="Edit user record" class="p-1.5 text-slate-400 hover:text-emerald-400 hover:bg-white/[0.05] rounded-lg border border-transparent hover:border-white/10 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <!-- Delete Icon Button -->
                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Permanently delete user {{ addslashes($u->name) }} ({{ $u->university_id }})?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete user" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg border border-transparent hover:border-rose-500/20 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                    No users found matching your search and filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-white/[0.06]">
                {{ $users->links() }}
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: Edit User                           -->
        <!-- ========================================== -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="editModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/[0.08] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 sm:p-7">
                    <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                        <div>
                            <h3 class="text-base font-semibold text-white tracking-tight">Edit User Account</h3>
                            <p class="text-xs text-slate-400" x-text="'Updating ' + editUser.name + ' (' + editUser.university_id + ')'"></p>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'/admin/users/' + editUser.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Full Name</label>
                                <input type="text" name="name" x-model="editUser.name" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all" />
                            </div>

                            <div>
                                <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">University ID</label>
                                <input type="text" name="university_id" x-model="editUser.university_id" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono transition-all" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Email Address</label>
                                <input type="email" name="email" x-model="editUser.email" placeholder="user@university.edu.et" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all" />
                            </div>

                            <div>
                                <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Assigned Role</label>
                                <select name="role" x-model="editUser.role" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                    <option value="student">Student Resident</option>
                                    <option value="proctor">Block Proctor</option>
                                    <option value="proctor_manager">Proctor Manager</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Gender</label>
                                <select name="gender" x-model="editUser.gender" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                    <option value="">Unspecified</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Department</label>
                                <input type="text" name="department" x-model="editUser.department" placeholder="e.g. Computer Science" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all" />
                            </div>

                            <div x-show="editUser.role === 'student'">
                                <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Academic Year</label>
                                <select name="academic_year" x-model="editUser.academic_year" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                    <option value="5">5th Year</option>
                                    <option value="6">6th Year</option>
                                    <option value="7">7th Year</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Registration Status</label>
                                <select name="registration_status" x-model="editUser.registration_status" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                                    <option value="completed">Completed (Claimed)</option>
                                    <option value="pending">Pending (Pre-registered)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] uppercase font-semibold text-slate-400 mb-1.5 tracking-wider">Reset Password (Optional)</label>
                                <input type="password" name="password" placeholder="Leave empty to keep current" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all" />
                            </div>
                        </div>

                        <!-- Active / Inactive Switch -->
                        <div class="pt-3 border-t border-white/[0.06] flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-semibold text-white">Account Activation Status</span>
                                <span class="text-[11px] text-slate-400">Deactivated users cannot authenticate or access the system.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="editUser.is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-white/[0.06]">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98]">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: Bulk Delete by ID Range             -->
        <!-- ========================================== -->
        <div x-show="bulkModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="bulkModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-rose-500/30 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 sm:p-7">
                    <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-rose-500/10 rounded-xl text-rose-400 border border-rose-500/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-white tracking-tight">Bulk Delete Users by ID Range</h3>
                                <p class="text-xs text-slate-400">Delete large batches of users in a single operation.</p>
                            </div>
                        </div>
                        <button type="button" @click="bulkModalOpen = false" class="text-slate-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('admin.users.bulk_destroy') }}" method="POST" class="mt-4 space-y-4" onsubmit="return confirm('WARNING: Are you absolutely sure you want to permanently delete these users? This cannot be undone.');">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-white mb-1.5">
                                Enter User ID Range or List
                            </label>
                            <input type="text" name="id_range" required placeholder="e.g. 2000-3000 or 1001, 1002, 1005"
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-rose-500 focus:ring-1 focus:ring-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono transition-all" />
                            <p class="text-[11px] text-slate-400 mt-1.5">
                                Supports numeric ranges (e.g. <span class="text-emerald-400 font-mono">2000-3000</span> to delete 1,000+ users at once) or comma-separated lists of IDs.
                            </p>
                        </div>

                        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-300 space-y-1">
                            <p class="font-semibold flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                High-Impact Administrative Action
                            </p>
                            <p class="text-[11px] text-rose-300/80 leading-relaxed">
                                Matching user accounts and their associated records will be permanently removed. The current administrator account is automatically protected from deletion.
                            </p>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-white/[0.06]">
                            <button type="button" @click="bulkModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 rounded-xl transition-all shadow-md active:scale-[0.98]">
                                Delete Matching Users
                            </button>
                        </div>
                    </form>
                </div>
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
                        <span class="block text-[11px] font-semibold uppercase text-white tracking-wider">Import Filters & Range Selection (Optional)</span>
                        <p class="text-[10px] text-slate-400">Use these criteria to import subsets from large registrar files (e.g. 10,000 students).</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Import by ID Range -->
                            <div>
                                <label class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Import by ID Range</label>
                                <input type="text" name="id_range" placeholder="e.g. 3000-4000 or WLD/1000-WLD/2000" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white" />
                                <span class="text-[9px] text-slate-500">Only rows with ID in this range</span>
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
                                <input type="text" name="department" placeholder="-- All or e.g. Computer Science --" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white" />
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
