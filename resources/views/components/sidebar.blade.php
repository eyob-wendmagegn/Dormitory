@php
    $role = auth()->user()->role;
@endphp

<!-- Fixed Sidebar Desktop / Drawer Container -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-[#111726]/95 backdrop-blur-xl border-r border-white/[0.06] transition-transform duration-200 ease-in-out flex flex-col justify-between shadow-2xl">
    
    <!-- Top Branding & Navigation -->
    <div class="flex flex-col h-full overflow-y-auto custom-sidebar-scroll">
        <!-- Sidebar Brand with Authentic Logo -->
        <div class="h-16 px-5 border-b border-white/[0.06] flex items-center justify-between sticky top-0 bg-[#111726]/95 backdrop-blur-xl z-10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.jpg') }}" alt="Woldia University" class="w-10 h-10 object-contain rounded-lg  border-white/[0.06] bg-white/5 p-0.5 group-hover:border-emerald-500/40 transition-colors" />
                <div class="leading-tight">
                    <span class="text-xs font-bold text-white block">Woldia University</span>
                    <span class="text-[10px] text-emerald-400 block uppercase tracking-wider font-semibold">Dorm System</span>
                </div>
            </a>
            <!-- Mobile Close Button -->
            <button type="button" @click="sidebarOpen = false" class="md:hidden text-[#9CA3AF] hover:text-white p-1 rounded-lg hover:bg-white/[0.04]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation Menu based on Role -->
        <nav class="p-4 space-y-1.5 flex-1">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 mb-2">
                Main Menu
            </div>

            {{-- 1. ADMINISTRATOR NAVIGATION --}}
            @if ($role === 'admin')
                <x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </x-slot:icon>
                    Dashboard
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </x-slot:icon>
                    User Management
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.preregister.index')" :active="request()->routeIs('admin.preregister.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </x-slot:icon>
                    Pre-Registered Users
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.students.index')" :active="request()->routeIs('admin.students.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                    </x-slot:icon>
                    Student Information
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.blocks.index')" :active="request()->routeIs('admin.blocks.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </x-slot:icon>
                    Dormitory Blocks
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.rooms.index')" :active="request()->routeIs('admin.rooms.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </x-slot:icon>
                    Rooms
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.allocations.index')" :active="request()->routeIs('admin.allocations.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </x-slot:icon>
                    Allocations
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.comments.index')" :active="request()->routeIs('admin.comments.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </x-slot:icon>
                    Comments
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </x-slot:icon>
                    Reports
                </x-sidebar-link>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-3 pb-1">
                    Curfew & Security
                </div>

                <x-sidebar-link :href="route('admin.attendance.index')" :active="request()->routeIs('admin.attendance.index')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </x-slot:icon>
                    Curfew Monitoring
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.leaves.index')" :active="request()->routeIs('admin.leaves.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </x-slot:icon>
                    Campus Gate Passes
                </x-sidebar-link>

                <x-sidebar-link :href="route('admin.attendance.reports')" :active="request()->routeIs('admin.attendance.reports')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </x-slot:icon>
                    Attendance Analytics
                </x-sidebar-link>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-3 pb-1">
                    System Maintenance
                </div>

                <x-sidebar-link :href="route('admin.backups.index')" :active="request()->routeIs('admin.backups.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    </x-slot:icon>
                    Database Backups
                </x-sidebar-link>

            {{-- 2. PROCTOR MANAGER NAVIGATION --}}
            @elseif ($role === 'proctor_manager')
                <x-sidebar-link :href="route('manager.dashboard')" :active="request()->routeIs('manager.dashboard')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </x-slot:icon>
                    Dashboard
                </x-sidebar-link>

                <x-sidebar-link :href="route('manager.blocks.index')" :active="request()->routeIs('manager.blocks.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </x-slot:icon>
                    Dormitory Blocks
                </x-sidebar-link>

                <x-sidebar-link :href="route('manager.rooms.index')" :active="request()->routeIs('manager.rooms.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </x-slot:icon>
                    Rooms
                </x-sidebar-link>

                <x-sidebar-link :href="route('manager.assignments.index')" :active="request()->routeIs('manager.assignments.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </x-slot:icon>
                    Proctor Assignments
                </x-sidebar-link>

                <x-sidebar-link :href="route('manager.students.index')" :active="request()->routeIs('manager.students.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                    </x-slot:icon>
                    Student Information
                </x-sidebar-link>

                <x-sidebar-link :href="route('manager.comments.index')" :active="request()->routeIs('manager.comments.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </x-slot:icon>
                    Comments
                </x-sidebar-link>

                <x-sidebar-link :href="route('manager.reports.index')" :active="request()->routeIs('manager.reports.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </x-slot:icon>
                    Reports
                </x-sidebar-link>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-3 pb-1">
                    Curfew & Security
                </div>

                <x-sidebar-link :href="route('manager.attendance.index')" :active="request()->routeIs('manager.attendance.index')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </x-slot:icon>
                    Curfew Monitoring
                </x-sidebar-link>

                <x-sidebar-link :href="route('manager.leaves.index')" :active="request()->routeIs('manager.leaves.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </x-slot:icon>
                    Gate Passes Oversight
                </x-sidebar-link>

                <x-sidebar-link :href="route('manager.attendance.reports')" :active="request()->routeIs('manager.attendance.reports')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </x-slot:icon>
                    Attendance Reports
                </x-sidebar-link>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-3 pb-1">
                    Assets & Directives
                </div>

                <x-sidebar-link :href="route('manager.assets.index')" :active="request()->routeIs('manager.assets.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </x-slot:icon>
                    Assets & Inventory
                </x-sidebar-link>

                <x-sidebar-link :href="route('manager.notices.index')" :active="request()->routeIs('manager.notices.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </x-slot:icon>
                    Internal Notices
                </x-sidebar-link>

            {{-- 3. PROCTOR NAVIGATION --}}
            @elseif ($role === 'proctor')
                <x-sidebar-link :href="route('proctor.dashboard')" :active="request()->routeIs('proctor.dashboard')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </x-slot:icon>
                    Dashboard
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.blocks.index')" :active="request()->routeIs('proctor.blocks.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </x-slot:icon>
                    My Blocks
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.rooms.index')" :active="request()->routeIs('proctor.rooms.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </x-slot:icon>
                    Rooms
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.allocate.index')" :active="request()->routeIs('proctor.allocate.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </x-slot:icon>
                    Student Allocation
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.students.index')" :active="request()->routeIs('proctor.students.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                    </x-slot:icon>
                    Student Information
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.allocated.index')" :active="request()->routeIs('proctor.allocated.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </x-slot:icon>
                    Allocated Students
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.comments.index')" :active="request()->routeIs('proctor.comments.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </x-slot:icon>
                    Comments
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.reports.index')" :active="request()->routeIs('proctor.reports.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </x-slot:icon>
                    Reports
                </x-sidebar-link>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-3 pb-1">
                    Curfew & Security
                </div>

                <x-sidebar-link :href="route('proctor.attendance.index')" :active="request()->routeIs('proctor.attendance.index')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </x-slot:icon>
                    Curfew Rounds
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.leaves.index')" :active="request()->routeIs('proctor.leaves.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </x-slot:icon>
                    Leave Requests
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.attendance.reports')" :active="request()->routeIs('proctor.attendance.reports')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </x-slot:icon>
                    Attendance Log
                </x-sidebar-link>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-3 pb-1">
                    Assets & Directives
                </div>

                <x-sidebar-link :href="route('proctor.assets.index')" :active="request()->routeIs('proctor.assets.index')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </x-slot:icon>
                    Assets & Stock
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.assets.clearance')" :active="request()->routeIs('proctor.assets.clearance')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </x-slot:icon>
                    Student Clearance
                </x-sidebar-link>

                <x-sidebar-link :href="route('proctor.notices.index')" :active="request()->routeIs('proctor.notices.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </x-slot:icon>
                    Notices & Memos
                </x-sidebar-link>

            {{-- 4. STUDENT NAVIGATION --}}
            @elseif ($role === 'student')
                <x-sidebar-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </x-slot:icon>
                    Dashboard
                </x-sidebar-link>

                <x-sidebar-link :href="route('student.dorm.index')" :active="request()->routeIs('student.dorm.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </x-slot:icon>
                    My Dorm
                </x-sidebar-link>

                <x-sidebar-link :href="route('student.room.index')" :active="request()->routeIs('student.room.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </x-slot:icon>
                    Room Information
                </x-sidebar-link>

                <x-sidebar-link :href="route('student.comments.create')" :active="request()->routeIs('student.comments.create')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </x-slot:icon>
                    Submit Comment
                </x-sidebar-link>

                <x-sidebar-link :href="route('student.comments.index')" :active="request()->routeIs('student.comments.index')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </x-slot:icon>
                    My Comments
                </x-sidebar-link>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-3 pb-1">
                    Curfew & Gate Passes
                </div>

                <x-sidebar-link :href="route('student.attendance.index')" :active="request()->routeIs('student.attendance.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </x-slot:icon>
                    My Attendance
                </x-sidebar-link>

                <x-sidebar-link :href="route('student.leaves.index')" :active="request()->routeIs('student.leaves.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </x-slot:icon>
                    Gate Pass & Leaves
                </x-sidebar-link>

                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-3 pb-1">
                    Assets & Notices
                </div>

                <x-sidebar-link :href="route('student.assets.index')" :active="request()->routeIs('student.assets.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </x-slot:icon>
                    My Dorm Assets
                </x-sidebar-link>

                <x-sidebar-link :href="route('student.notices.index')" :active="request()->routeIs('student.notices.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </x-slot:icon>
                    Dorm Notices
                </x-sidebar-link>
            @endif

            <div class="pt-4 mt-4 border-t border-white/[0.06]">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 mb-2">
                    Account
                </div>
                <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </x-slot:icon>
                    Settings & Profile
                </x-sidebar-link>
            </div>
        </nav>

        <!-- Bottom User Card -->
        <div class="p-4 border-t border-white/[0.06] bg-[#0B0F17]/60 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <img class="w-9 h-9 rounded-lg object-cover border border-white/[0.06]" src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" />
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-emerald-400 font-semibold truncate">{{ auth()->user()->university_id }} &bull; <span class="capitalize">{{ auth()->user()->gender ?? 'N/A' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</aside>
