<x-app-layout title="Settings & Profile">
    @php
        $role = $user->role;
        $badgeColors = [
            'admin' => 'bg-violet-500/10 text-violet-400 border-violet-500/20',
            'proctor_manager' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
            'proctor' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'student' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        ];
        $roleNames = [
            'admin' => 'Administrator',
            'proctor_manager' => 'Proctor Manager',
            'proctor' => 'Block Proctor',
            'student' => 'Student Resident',
        ];
        $isStudentAllocated = $user->isStudent() && $user->activeAllocation()->exists();
        $allocation = $user->isStudent() ? $user->activeAllocation : null;
    @endphp

    <div class="w-full space-y-5 pb-2" x-data="{
        showCurrentPw: false,
        showNewPw: false,
        showConfirmPw: false,
        photoPreview: '{{ $user->avatar_url }}',
        handlePhotoSelect(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => { this.photoPreview = e.target.result; };
                reader.readAsDataURL(file);
            }
        }
    }">

        <!-- SECTION 1: IDENTITY & PROFILE OVERVIEW BANNER (Role & Gender Prominently Displayed) -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6 relative z-10">
                <!-- User Avatar & Identity Details -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
                    <div class="relative shrink-0">
                        <img class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-white/10 shadow-lg"
                             :src="photoPreview" alt="{{ $user->name }}" />
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-[#121927] border-2 border-[#121927] flex items-center justify-center" title="Account Active">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        </span>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold text-white tracking-tight">{{ $user->name }}</h1>

                        <!-- Role Badge and Gender Badge in Profile -->
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <!-- Role Badge -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border {{ $badgeColors[$role] ?? 'bg-white/[0.05] text-slate-300 border-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span>{{ $roleNames[$role] ?? ucfirst($role) }}</span>
                            </span>

                            <!-- Gender Badge in Profile -->
                            @if($user->gender)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium uppercase tracking-wider {{ $user->gender === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                    @if($user->gender === 'male')
                                        <svg class="w-3.5 h-3.5 text-cyan-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 11-2 0V4.414l-3.293 3.293a6 6 0 11-1.414-1.414L14.586 3H11a1 1 0 01-1-1zM4 12a4 4 0 108 0 4 4 0 00-8 0z"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a6 6 0 100 12 1 1 0 011 1v2h2a1 1 0 110 2h-2v1a1 1 0 11-2 0v-1H7a1 1 0 110-2h2v-2a1 1 0 011-1 6 6 0 000-12zm-4 6a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd"/></svg>
                                    @endif
                                    <span>{{ $user->gender }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-white/[0.05] text-slate-400 border border-white/10">
                                    <span>Gender Unset</span>
                                </span>
                            @endif

                            <!-- Account Status -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 capitalize">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span>{{ $user->registration_status }}</span>
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 pt-1 text-xs text-slate-400">
                            <span class="font-mono text-emerald-400 font-medium bg-[#0F1420]/80 px-2.5 py-1 rounded-lg border border-white/[0.06]">
                                ID: {{ $user->university_id }}
                            </span>
                            <span class="text-slate-300 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $user->email ?? 'No email set' }}
                            </span>
                            @if($user->department)
                                <span class="text-slate-300 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    {{ $user->department }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Contextual Placement Info -->
                <div class="sm:text-right shrink-0">
                    @if($user->isStudent())
                        <div class="p-3.5 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05] text-left sm:text-right">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Dormitory Room</span>
                            @if($allocation && $allocation->room)
                                <p class="text-sm font-semibold text-white mt-0.5">
                                    Block {{ $allocation->room->block->block_code }} &bull; Room {{ $allocation->room->room_number }}
                                </p>
                                <span class="text-[11px] text-emerald-400 block capitalize">{{ $allocation->room->block->name }}</span>
                            @else
                                <p class="text-xs font-medium text-amber-400 mt-0.5">Awaiting Allocation</p>
                            @endif
                        </div>
                    @elseif($user->isProctor())
                        <div class="p-3.5 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05] text-left sm:text-right">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Assigned Blocks</span>
                            <p class="text-sm font-semibold text-white mt-0.5">{{ $user->activeProctorAssignments->count() }} Managed</p>
                            <span class="text-[11px] text-emerald-400 block font-medium">On Duty</span>
                        </div>
                    @else
                        <div class="p-3.5 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05] text-left sm:text-right">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Portal Scope</span>
                            <p class="text-sm font-semibold text-white mt-0.5">Campus Dormitory</p>
                            <span class="text-[11px] text-emerald-400 block font-medium">Institutional Access</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- SECTION 2: PROFILE PHOTO MANAGEMENT FORM -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.06] mb-5">
                <div>
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Profile Photo</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Upload a clear portrait photo for official identification across the dormitory system.</p>
                </div>
                <span class="text-[10px] font-mono text-slate-400 bg-white/[0.05] px-2.5 py-1 rounded-full border border-white/10">
                    Max 3MB
                </span>
            </div>

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <!-- Preview Thumbnail -->
                <div class="relative shrink-0">
                    <img class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-2 border-white/10 shadow-md"
                         :src="photoPreview" alt="{{ $user->name }}" />
                </div>

                <!-- Upload Form & Actions -->
                <div class="flex-1 w-full space-y-4 text-center sm:text-left">
                    <p class="text-xs text-slate-400">
                        Supported image formats: <strong class="text-white">JPEG, PNG, WEBP</strong>. Clear front-facing headshots are strongly recommended.
                    </p>

                    <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-3">
                        @csrf
                        <div class="w-full sm:w-auto flex-1">
                            <input type="file" name="avatar" required accept="image/jpeg,image/png,image/webp"
                                   @change="handlePhotoSelect($event)"
                                   class="w-full text-xs text-slate-300 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-white/[0.06] file:text-white hover:file:bg-white/[0.1] file:cursor-pointer border border-white/[0.08] rounded-xl bg-[#0F1420] p-1.5 focus:outline-none" />
                        </div>

                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 shrink-0 flex items-center justify-center gap-1.5 active:scale-[0.98]">
                            <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Upload Photo</span>
                        </button>
                    </form>
                    @error('avatar') <p class="text-xs text-rose-400 font-semibold">{{ $message }}</p> @enderror

                    @if ($user->profile_photo_path)
                        <div class="pt-1">
                            <form method="POST" action="{{ route('profile.avatar.destroy') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Restore default initials avatar and delete your custom photo?');"
                                        class="text-xs font-medium text-rose-400 hover:text-rose-300 hover:underline inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Remove custom photo & restore default initials avatar</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- SECTION 3: PERSONAL INFORMATION & DEMOGRAPHICS FORM -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.06] mb-6">
                <div>
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Personal Details</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Manage your legal full name, institutional email, academic affiliation, and gender.</p>
                </div>
                <span class="text-[10px] font-medium text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                    Profile Data
                </span>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <!-- Row 1: Full Name & University ID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
                            <span>Full Legal Name</span>
                            <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-3 text-sm text-white transition-all shadow-inner placeholder-slate-500"
                               placeholder="e.g. Eyob Solomon" />
                        @error('name') <p class="text-xs text-rose-400 mt-1.5 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
                            <span>University ID</span>
                            <span class="text-[10px] text-slate-500 font-normal">Permanent Institutional Record</span>
                        </label>
                        <div class="relative">
                            <input type="text" value="{{ $user->university_id }}" disabled readonly
                                   class="w-full bg-[#0F1420]/50 border border-white/[0.06] rounded-xl px-4 py-3 text-sm text-emerald-400 font-mono cursor-not-allowed opacity-90 shadow-inner" />
                            <span class="absolute right-3 top-3 text-[10px] font-medium px-2 py-0.5 rounded-lg bg-white/[0.05] text-slate-400 border border-white/10">
                                Locked
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Email & Department -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
                            <span>Institutional Email</span>
                            <span class="text-rose-400">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-3 text-sm text-white transition-all shadow-inner placeholder-slate-500"
                               placeholder="user@woldia.edu.et" />
                        @error('email') <p class="text-xs text-rose-400 mt-1.5 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            <span>Department / Faculty</span>
                        </label>
                        <input type="text" name="department" value="{{ old('department', $user->department) }}"
                               class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-3 text-sm text-white transition-all shadow-inner placeholder-slate-500"
                               placeholder="e.g. Computer Science, Medicine" />
                        @error('department') <p class="text-xs text-rose-400 mt-1.5 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Row 3: Gender & Role Display in Profile Details -->
                <div class="p-5 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05] space-y-4">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-white block">Demographic Gender & System Role</span>
                        <p class="text-[11px] text-slate-400 mt-0.5">Demographic gender governs dormitory block zoning and allocation verification.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-1">
                        <!-- Gender Selection -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-2">Demographic Gender</label>
                            @if($isStudentAllocated)
                                <!-- Locked for allocated student -->
                                <div class="flex items-center gap-2.5 p-3 rounded-xl bg-[#0F1420] border border-white/[0.06]">
                                    <input type="hidden" name="gender" value="{{ $user->gender }}" />
                                    @if($user->gender === 'male')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 uppercase">
                                            ♂ Male
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">
                                            ♀ Female
                                        </span>
                                    @endif
                                    <span class="text-[11px] text-slate-400">Locked to active dorm allocation</span>
                                </div>
                                <p class="text-[10px] text-amber-400 mt-1.5">
                                    Gender is locked to assigned Room {{ $allocation->room->room_number ?? '' }} in Block {{ $allocation->room->block->block_code ?? '' }}.
                                </p>
                            @else
                                <!-- Selectable Gender -->
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer transition-all {{ old('gender', $user->gender) === 'male' ? 'bg-cyan-500/10 border-cyan-500/40 text-cyan-300 font-semibold' : 'bg-[#0F1420] border-white/[0.06] text-slate-400 hover:border-white/20' }}">
                                        <input type="radio" name="gender" value="male" class="sr-only" {{ old('gender', $user->gender) === 'male' ? 'checked' : '' }} />
                                        <svg class="w-4 h-4 text-cyan-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 11-2 0V4.414l-3.293 3.293a6 6 0 11-1.414-1.414L14.586 3H11a1 1 0 01-1-1zM4 12a4 4 0 108 0 4 4 0 00-8 0z"/></svg>
                                        <span>Male</span>
                                    </label>

                                    <label class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer transition-all {{ old('gender', $user->gender) === 'female' ? 'bg-rose-500/10 border-rose-500/40 text-rose-300 font-semibold' : 'bg-[#0F1420] border-white/[0.06] text-slate-400 hover:border-white/20' }}">
                                        <input type="radio" name="gender" value="female" class="sr-only" {{ old('gender', $user->gender) === 'female' ? 'checked' : '' }} />
                                        <svg class="w-4 h-4 text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a6 6 0 100 12 1 1 0 011 1v2h2a1 1 0 110 2h-2v1a1 1 0 11-2 0v-1H7a1 1 0 110-2h2v-2a1 1 0 011-1 6 6 0 000-12zm-4 6a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd"/></svg>
                                        <span>Female</span>
                                    </label>
                                </div>
                            @endif
                            @error('gender') <p class="text-xs text-rose-400 mt-1.5 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <!-- Assigned Role Display -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-2">Assigned Institutional Role</label>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-[#0F1420] border border-white/[0.06]">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border {{ $badgeColors[$role] ?? 'bg-white/[0.05] text-slate-300 border-white/10' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span>{{ $roleNames[$role] ?? ucfirst($role) }}</span>
                                </span>
                                <span class="text-[10px] text-slate-500 font-mono">System Role</span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1.5">Roles and administrative permissions are governed by campus authorities.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2 active:scale-[0.98]">
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Personal Details</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- SECTION 4: SECURITY & PASSWORD UPDATE FORM -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.06] mb-6">
                <div>
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Change Password</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Keep your account credentials secure. Minimum 8 characters recommended.</p>
                </div>
                <span class="text-[10px] font-semibold text-slate-400 bg-white/[0.05] px-2.5 py-1 rounded-full border border-white/10">
                    Security
                </span>
            </div>

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Current Password with Toggle -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        <span>Current Password</span>
                    </label>
                    <div class="relative">
                        <input :type="showCurrentPw ? 'text' : 'password'" name="current_password" required
                               class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-3 pr-11 text-sm text-white transition-all shadow-inner placeholder-slate-500"
                               placeholder="Enter your current password" />
                        <button type="button" @click="showCurrentPw = !showCurrentPw"
                                class="absolute right-3 top-3.5 text-slate-400 hover:text-white transition-colors">
                            <svg x-show="!showCurrentPw" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showCurrentPw" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                    @error('current_password') <p class="text-xs text-rose-400 mt-1.5 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- New Password & Confirm Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            <span>New Password</span>
                        </label>
                        <div class="relative">
                            <input :type="showNewPw ? 'text' : 'password'" name="password" required
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-3 pr-11 text-sm text-white transition-all shadow-inner placeholder-slate-500"
                                   placeholder="At least 8 characters" />
                            <button type="button" @click="showNewPw = !showNewPw"
                                    class="absolute right-3 top-3.5 text-slate-400 hover:text-white transition-colors">
                                <svg x-show="!showNewPw" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showNewPw" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        @error('password') <p class="text-xs text-rose-400 mt-1.5 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            <span>Confirm New Password</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPw ? 'text' : 'password'" name="password_confirmation" required
                                   class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-3 pr-11 text-sm text-white transition-all shadow-inner placeholder-slate-500"
                                   placeholder="Repeat new password" />
                            <button type="button" @click="showConfirmPw = !showConfirmPw"
                                    class="absolute right-3 top-3.5 text-slate-400 hover:text-white transition-colors">
                                <svg x-show="!showConfirmPw" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showConfirmPw" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2 active:scale-[0.98]">
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- SECTION 5: OFFICIAL INSTITUTIONAL RECORD -->
        <div class="p-6 sm:p-7 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.06] mb-5">
                <div>
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Official University Record</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Audited demographic and institutional identification parameters.</p>
                </div>
                <span class="text-[10px] text-slate-400 font-mono bg-white/[0.05] px-2.5 py-1 rounded-full border border-white/10">
                    Institutional
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">University ID</span>
                    <p class="text-base font-semibold text-emerald-400 font-mono mt-1">{{ $user->university_id }}</p>
                </div>

                <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Assigned Role</span>
                    <p class="text-base font-semibold text-white mt-1 capitalize">{{ $roleNames[$role] ?? str_replace('_', ' ', $user->role) }}</p>
                </div>

                <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Demographic Gender</span>
                    <p class="text-base font-semibold mt-1 uppercase {{ $user->gender === 'male' ? 'text-cyan-400' : 'text-rose-400' }}">
                        {{ $user->gender ?? 'Not Set' }}
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Account Status</span>
                    <p class="text-base font-semibold text-emerald-400 mt-1 capitalize">{{ $user->registration_status }}</p>
                </div>

                @if ($user->department)
                    <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Department</span>
                        <p class="text-base font-semibold text-white mt-1">{{ $user->department }}</p>
                    </div>
                @endif

                @if ($user->isStudent())
                    <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Academic Year</span>
                        <p class="text-base font-semibold text-white mt-1">{{ $user->academic_year_label ?? 'Not Assigned' }}</p>
                    </div>
                @endif

                <div class="p-4 rounded-2xl bg-[#0F1420]/60 border border-white/[0.05]">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Registered On</span>
                    <p class="text-base font-semibold text-white mt-1">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
