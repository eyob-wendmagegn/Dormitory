@props(['title' => 'Dashboard'])

<header class="h-16 bg-[#111726]/90 backdrop-blur-xl border-b border-white/[0.06] fixed top-0 right-0 left-0 md:left-64 z-30 flex items-center justify-between px-4 sm:px-6 shadow-sm shadow-black/10">
    <!-- Left Section: Sidebar Toggle & Page Title (Single Authentic Logo in Sidebar) -->
    <div class="flex items-center gap-3 sm:gap-4">
        <!-- Mobile Drawer Toggle Button -->
        <button type="button" @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-400 hover:text-white p-2 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div>
            <h1 class="text-base sm:text-lg font-semibold text-white tracking-tight leading-none flex items-center gap-2">
                {{ $title }}
            </h1>
            <span class="text-xs text-slate-400 leading-none block mt-1 font-normal">
                Woldia University Dormitory System
            </span>
        </div>
    </div>

    <!-- Right Section: Theme Toggle, Notifications Bell & User Profile Dropdown -->
    <div class="flex items-center gap-3 sm:gap-4">
        <!-- Dark / Light Theme Toggle Button -->
        <button type="button"
                onclick="toggleTheme()"
                title="Toggle Dark / Light Mode"
                aria-label="Toggle Dark / Light Mode"
                class="p-2 rounded-xl text-slate-400 hover:text-white bg-white/[0.03] hover:bg-white/[0.08] border border-white/[0.06] transition-all cursor-pointer">
            <!-- Sun Icon (shown in Dark Mode -> click to switch to Light/White) -->
            <svg class="w-4 h-4 theme-icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <!-- Moon Icon (shown in Light Mode -> click to switch back to Dark/Black) -->
            <svg class="w-4 h-4 theme-icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </button>

        @php
            $role = auth()->user()->role;
            $badgeColors = [
                'admin' => 'bg-purple-500/10 text-purple-300 border-purple-500/20',
                'proctor_manager' => 'bg-blue-500/10 text-blue-300 border-blue-500/20',
                'proctor' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                'student' => 'bg-amber-500/10 text-amber-300 border-amber-500/20',
            ];
            $roleNames = [
                'admin' => 'Administrator',
                'proctor_manager' => 'Proctor Manager',
                'proctor' => 'Block Proctor',
                'student' => 'Student Resident',
            ];
            $unreadAlerts = auth()->user()->unreadAttendanceAlerts()->take(5)->get();
            $unreadCount = auth()->user()->unreadAttendanceAlerts()->count();
        @endphp

        <!-- Notifications Bell Dropdown -->
        <div class="relative" x-data="{ alertOpen: false }" @click.outside="alertOpen = false">
            <button @click="alertOpen = !alertOpen" class="relative p-2 rounded-xl text-slate-400 hover:text-white bg-white/[0.03] hover:bg-white/[0.08] border border-white/[0.06] transition-all" title="Curfew Notices & Alerts">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($unreadCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white rounded-full text-[9px] font-bold flex items-center justify-center shadow-lg shadow-rose-500/50 animate-pulse">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </button>

            <!-- Alerts Dropdown Menu -->
            <div x-show="alertOpen"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl py-2 z-50 text-xs shadow-black/40"
                 style="display: none;">
                <div class="px-4 py-2.5 border-b border-white/[0.06] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-white text-xs">Notifications & Curfew Notices</span>
                        @if($unreadCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                {{ $unreadCount }} new
                            </span>
                        @endif
                    </div>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('alerts.read_all') }}">
                            @csrf
                            <button type="submit" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition-colors">
                                Mark all read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-72 overflow-y-auto divide-y divide-white/[0.04]">
                    @forelse($unreadAlerts as $alert)
                        <div class="p-3.5 hover:bg-white/[0.03] transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-semibold text-slate-100 text-xs block">{{ $alert->title }}</span>
                                <span class="text-[10px] text-slate-500 font-mono shrink-0">{{ $alert->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">{{ $alert->message }}</p>
                            <div class="mt-2.5 flex items-center justify-between">
                                @if($alert->action_url)
                                    <form method="POST" action="{{ route('alerts.read', $alert) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium flex items-center gap-1 transition-colors">
                                            <span>View Details</span> &rarr;
                                        </button>
                                    </form>
                                @else
                                    <span></span>
                                @endif
                                <form method="POST" action="{{ route('alerts.read', $alert) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[11px] text-slate-500 hover:text-slate-300 transition-colors">
                                        Dismiss
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-7 px-4 text-center text-slate-400">
                            <svg class="w-7 h-7 text-slate-600 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-xs font-medium text-white block">All caught up</span>
                            <span class="text-[11px] text-slate-500">No unread curfew notices or alerts.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown Button -->
        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
            <button @click="open = !open" class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-white/[0.04] border border-transparent hover:border-white/[0.06] transition-all focus:outline-none">
                <img class="h-8 w-8 rounded-xl object-cover border border-white/[0.08]" src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" />
                <div class="hidden md:block text-left">
                    <span class="block text-xs font-semibold text-white leading-tight">{{ auth()->user()->name }}</span>
                    <span class="block text-[10px] text-slate-400 leading-tight font-mono mt-0.5">ID: {{ auth()->user()->university_id }}</span>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <!-- Dropdown Menu: Displays User Identity, Role and Gender in Profile -->
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-72 sm:w-80 rounded-2xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl p-3 z-50 text-xs shadow-black/40"
                 style="display: none;">
                
                <!-- Profile Header Information Card -->
                <div class="p-3.5 rounded-xl bg-[#0B0F17]/80 border border-white/[0.06] mb-2">
                    <div class="flex items-center gap-3">
                        <img class="w-10 h-10 rounded-xl object-cover border border-white/[0.08] shrink-0" src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" />
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-white truncate text-xs">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-emerald-400 font-mono font-medium truncate">ID: {{ auth()->user()->university_id }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email ?? 'No email registered' }}</p>
                        </div>
                    </div>

                    <!-- Role & Gender Badges (Frosted styling) -->
                    <div class="mt-3 pt-2.5 border-t border-white/[0.06] flex flex-wrap items-center gap-2">
                        <!-- Role Badge -->
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-medium border {{ $badgeColors[$role] ?? 'bg-white/10 text-white border-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ $roleNames[$role] ?? ucfirst($role) }}
                        </span>

                        <!-- Gender Badge -->
                        @if(auth()->user()->gender)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider {{ auth()->user()->gender === 'male' ? 'bg-cyan-500/10 text-cyan-300 border border-cyan-500/20' : 'bg-pink-500/10 text-pink-300 border border-pink-500/20' }}">
                                @if(auth()->user()->gender === 'male')
                                    <svg class="w-3 h-3 text-cyan-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 11-2 0V4.414l-3.293 3.293a6 6 0 11-1.414-1.414L14.586 3H11a1 1 0 01-1-1zM4 12a4 4 0 108 0 4 4 0 00-8 0z"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-pink-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a6 6 0 100 12 1 1 0 011 1v2h2a1 1 0 110 2h-2v1a1 1 0 11-2 0v-1H7a1 1 0 110-2h2v-2a1 1 0 011-1 6 6 0 000-12zm-4 6a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd"/></svg>
                                @endif
                                <span>{{ auth()->user()->gender }}</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="space-y-1">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-white/[0.06] transition-all group">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="font-medium">Settings & Profile</span>
                    </a>

                    <div class="border-t border-white/[0.06] my-1"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-all">
                            <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="font-medium">Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
