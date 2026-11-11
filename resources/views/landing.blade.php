<x-guest-layout title="Woldia University | Dormitory Management System">
<div x-data="{ showPolicyModal: false, policyTab: 'terms' }">
    <!-- Top Sticky/Absolute Navbar -->
    <header class="sticky top-0 z-40 bg-[#111726]/85 backdrop-blur-xl border-b border-white/[0.06] transition-all animate-slow-entrance">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand / University Identity -->
            <a href="{{ route('landing') }}" class="flex items-center gap-3.5 group">
                <img src="{{ asset('images/logo.jpg') }}" 
                     alt="Woldia University Logo" 
                     class="w-11 h-11 object-contain rounded-xl bg-white/5 p-1  border-white/10 group-hover:border-emerald-500/40 transition-colors " />
                <div class="leading-tight">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Woldia University</span>
                    <span class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                        Dormitory Management System
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                    </span>
                </div>
            </a>

            <!-- Desktop Navigation Links (Smooth-Scrolling Anchors) -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-400">
                <a href="#about" class="hover:text-white transition-colors">About</a>
                <a href="#features" class="hover:text-white transition-colors">Features</a>
                <a href="#stats" class="hover:text-white transition-colors">System Metrics</a>
                <a href="#how-it-works" class="hover:text-white transition-colors">How It Works</a>
                @if(isset($announcements) && $announcements->count() > 0)
                    <a href="#announcements" class="hover:text-white transition-colors">Notices</a>
                @endif
                <a href="#contact" class="hover:text-white transition-colors">Contact</a>
            </nav>

            <!-- Authentication Actions & Theme Toggle -->
            <div class="flex items-center gap-3">
                <!-- Dark / Light Theme Toggle Button -->
                <button type="button"
                        onclick="toggleTheme()"
                        title="Toggle Dark / Light Mode"
                        aria-label="Toggle Dark / Light Mode"
                        class="p-2.5 rounded-xl text-slate-400 hover:text-white bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.08] transition-all cursor-pointer">
                    <svg class="w-4 h-4 theme-icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg class="w-4 h-4 theme-icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2 active:scale-[0.98]">
                        <span>Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="text-sm font-medium text-slate-300 hover:text-white px-3.5 py-2 transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" 
                       class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-4 sm:px-5 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-1.5 active:scale-[0.98]">
                        <span>Claim Account</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Ultra High-Definition Hero Section with Crisp 4K Campus Photography -->
    <section class="relative bg-no-repeat bg-cover overflow-hidden theme-preserve-dark"
             style="background-image: url('{{ asset('images/landing.jpg') }}'); background-position: center 25%; image-rendering: -webkit-optimize-contrast;">
        <!-- Gentle Radial Vignette for Center Focus -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-transparent via-[#0B0F17]/15 to-[#0B0F17]/90 pointer-events-none"></div>

        <!-- Delicate Luminous Photographic Scrim (Fades smoothly to canvas color at bottom) -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#0B0F17]/50 via-[#0B0F17]/30 to-[#0B0F17] hero-bottom-scrim"></div>

        <!-- Hero Content Container (No empty top void, slow graceful entrance) -->
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10 pb-20 sm:pb-24 text-center">
            <!-- Official University Motto Badge (Stagger 1) -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#111726]/85 backdrop-blur-md border border-emerald-500/30 text-xs font-semibold text-emerald-300 mb-5 shadow-lg shadow-black/30 animate-slow-entrance stagger-1">
                <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.9)]"></span>
                <span>Woldia University &bull; Motto: &ldquo;Open Mind, Open Eyes&rdquo;</span>
            </div>

            <!-- Big Welcome Heading on the Image (Stagger 2) -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight uppercase leading-[1.15] max-w-4xl mx-auto drop-shadow-[0_4px_18px_rgba(0,0,0,0.9)] animate-slow-entrance stagger-2">
                WELCOME TO <br class="hidden sm:inline" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-300 drop-shadow-[0_2px_12px_rgba(0,0,0,0.6)]">
                    WOLDIA UNIVERSITY
                </span>
                <span class="block text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white normal-case tracking-tight mt-2 drop-shadow-[0_4px_18px_rgba(0,0,0,0.9)]">
                    Dormitory Management System
                </span>
            </h1>

            <!-- Test-compliant lead subtext: 'Every student deserves' (Stagger 3) -->
            <p class="mt-5 text-base sm:text-lg text-slate-100 max-w-3xl mx-auto font-normal leading-relaxed drop-shadow-[0_2px_10px_rgba(0,0,0,0.95)] animate-slow-entrance stagger-3">
                Every student deserves a place to belong. Smart, transparent, and equitable dormitory management for Woldia University. Overseeing verified student rosters, automated gender segregation, nightly curfew rounds, and proctor assignments through one cohesive enterprise platform.
            </p>

            <!-- Call to Actions (Stagger 4) -->
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4 animate-slow-entrance stagger-4">
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold shadow-xl shadow-emerald-500/25 transition-all rounded-xl px-6 py-3.5 flex items-center gap-2 active:scale-[0.98]">
                        <span>Go to My Dashboard</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold shadow-xl shadow-emerald-500/25 transition-all rounded-xl px-6 py-3.5 flex items-center gap-2 active:scale-[0.98]">
                        <span>Sign In to Portal</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @endauth
                <a href="#about" 
                   class="bg-[#111726]/85 hover:bg-[#111726] text-white border border-white/15 backdrop-blur-md rounded-xl px-6 py-3.5 transition-all flex items-center gap-2 shadow-lg shadow-black/30">
                    <span>About University Life</span>
                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Live System Statistics Counter Strip (Stagger 5, Smooth Scroll Target) -->
    <section id="stats" class="relative z-20 -mt-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-28 animate-slow-entrance stagger-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Stat 1: Total Residents -->
            <div class="bg-[#111726]/85 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 hover:border-emerald-500/30 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Accommodated</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </div>
                <div class="text-3xl sm:text-4xl font-bold font-mono text-white tracking-tight">
                    {{ number_format($totalResidents ?? 0) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Verified Active Residents ({{ $stats['total_users'] ?? 0 }} Users)</p>
            </div>

            <!-- Stat 2: Dormitory Blocks -->
            <div class="bg-[#111726]/85 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 hover:border-emerald-500/30 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dormitory Blocks</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </div>
                <div class="text-3xl sm:text-4xl font-bold font-mono text-white tracking-tight">
                    {{ $totalBlocks ?? 0 }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Male &amp; Female Designated Halls</p>
            </div>

            <!-- Stat 3: Total Configured Rooms -->
            <div class="bg-[#111726]/85 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 hover:border-emerald-500/30 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Living Rooms</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </div>
                <div class="text-3xl sm:text-4xl font-bold font-mono text-white tracking-tight">
                    {{ number_format($totalRooms ?? 0) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Capacity-Governed Dorms</p>
            </div>

            <!-- Stat 4: Curfew & Safety Compliance Rate -->
            <div class="bg-[#111726]/85 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 hover:border-emerald-500/30 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Safety Compliance</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </div>
                <div class="text-3xl sm:text-4xl font-bold font-mono text-emerald-400 tracking-tight">
                    {{ $complianceRate ?? '0%' }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Curfew &amp; Gate Pass Audit</p>
            </div>
        </div>
    </section>

    <!-- About Section (Concise & Accurate From Woldia University History, Smooth Scroll Target) -->
    <section id="about" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-28">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold uppercase tracking-wider text-emerald-400">
                    <span>About Woldia University</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    Fostering Academic Excellence &amp; Modern Residence Life Since 2011
                </h2>
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                    Established in 2011 G.C. by the Government of Ethiopia on a 201-hectare campus in Woldia town (North Wollo Zone, Amhara Region), Woldia University operates under the inspiring motto <strong class="text-white">&ldquo;Open Mind, Open Eyes&rdquo;</strong>.
                </p>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Strategically connecting the cross-sectional corridors of Samara (Afar), Mekele (Tigray), Bahir Dar, and Dessie, the university has expanded across three campuses &mdash; Main Woldia Campus, Mersa Agricultural College, and St. Lalibela Institute &mdash; supported by 1,285+ faculty members and dedicated student service professionals.
                </p>

                <!-- Key Highlights Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <div class="p-4 rounded-2xl bg-[#111726]/60 border border-white/[0.06]">
                        <div class="text-emerald-400 font-bold text-lg">201 Hectares</div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Sprawling Main Campus in North Wollo</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-[#111726]/60 border border-white/[0.06]">
                        <div class="text-emerald-400 font-bold text-lg">3 Campuses</div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Woldia, Mersa &amp; St. Lalibela Institute</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-[#111726]/60 border border-white/[0.06]">
                        <div class="text-emerald-400 font-bold text-lg">1,285+ Staff</div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Dedicated Faculty &amp; Academic Staff</p>
                    </div>
                </div>
            </div>

            <!-- Feature Snapshot Card -->
            <div class="space-y-4">
                <div class="p-6 sm:p-7 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] space-y-3 shadow-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Equitable Placement Protocol</h3>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        No student can be placed manually outside verified university rosters. Bed placements automatically respect room capacity caps to prevent overpopulation.
                    </p>
                </div>

                <div class="p-6 sm:p-7 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] space-y-3 shadow-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Curfew &amp; Gate Pass Tracking</h3>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Daily evening attendance logs, authorized weekend gate passes, and automatic alert notifications keep administrators fully informed of campus residence status.
                    </p>
                </div>

                <div class="p-6 sm:p-7 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] space-y-3 shadow-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Direct Student Inquiries &amp; Maintenance</h3>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Students report plumbing, electricity, or living conditions issues directly from their portal with transparent proctor follow-ups and resolution timestamps.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 6-Card Feature Grid (Core Platform Capabilities, Smooth Scroll Target) -->
    <section id="features" class="py-20 border-t border-white/[0.06] bg-[#0E1420]/40 scroll-mt-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold uppercase tracking-widest text-emerald-400 mb-2">Enterprise Capabilities</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-white">Engineered for Academic Integrity and Residential Welfare</h3>
                <p class="text-sm text-slate-400 mt-3">A purpose-built suite of tools engineered specifically for university student residence operations.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <!-- Feature 1: Room Allocation -->
                <div class="bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 sm:p-8 hover:border-emerald-500/30 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-6 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-white mb-2.5">Room Allocation</h4>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4">
                            Intelligent bed placement algorithms that enforce gender zoning, cohort batching, and prevent room capacity breaches.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-white/[0.05] flex items-center gap-2 text-xs text-emerald-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Capacity Safeguards (Max 6 Beds)</span>
                    </div>
                </div>

                <!-- Feature 2: Attendance Tracking -->
                <div class="bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 sm:p-8 hover:border-emerald-500/30 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-6 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-white mb-2.5">Attendance Tracking</h4>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4">
                            Nightly roll call rosters with quick room batch verification, sequential room jump, and instant student excuse reconciliation.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-white/[0.05] flex items-center gap-2 text-xs text-emerald-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Real-Time Check-In Audits</span>
                    </div>
                </div>

                <!-- Feature 3: Curfew Monitoring -->
                <div class="bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 sm:p-8 hover:border-emerald-500/30 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-6 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-white mb-2.5">Curfew Monitoring</h4>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4">
                            Campus-wide curfew compliance monitoring with chronic absence detection, supervisor alerts, and authorized pass validation.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-white/[0.05] flex items-center gap-2 text-xs text-emerald-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Automated Supervisor Alerts</span>
                    </div>
                </div>

                <!-- Feature 4: Live Reports -->
                <div class="bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 sm:p-8 hover:border-emerald-500/30 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-6 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-white mb-2.5">Live Reports</h4>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4">
                            High-fidelity visual analytics, bed occupancy sparklines, absentee CSV exports, and shift handover summaries.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-white/[0.05] flex items-center gap-2 text-xs text-emerald-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Instant CSV Export Engine</span>
                    </div>
                </div>

                <!-- Feature 5: Role Permissions -->
                <div class="bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 sm:p-8 hover:border-emerald-500/30 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-6 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-white mb-2.5">Role Permissions</h4>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4">
                            Strict 4-tier separation of duties for Administrators, Proctor Managers, Hall Proctors, and Resident Students with zero cross-role leak.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-white/[0.05] flex items-center gap-2 text-xs text-emerald-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Isolated Scopes &amp; Gates</span>
                    </div>
                </div>

                <!-- Feature 6: Backups & Audit Security -->
                <div class="bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 sm:p-8 hover:border-emerald-500/30 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-6 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-white mb-2.5">Backups &amp; Audit Security</h4>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4">
                            Automated database snapshots, immutable event timelines, and permanent registrar ID locks ensure complete institutional compliance.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-white/[0.05] flex items-center gap-2 text-xs text-emerald-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Tamper-Resistant Records</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- System Workflow Section (Includes required 'id="how-it-works"', 'Step-by-Step Workflow', Smooth Scroll Target) -->
    <section id="how-it-works" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-28">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold uppercase tracking-wider text-emerald-400 mb-3">
                <span>Step-by-Step Workflow</span>
            </div>
            <h3 class="text-3xl sm:text-4xl font-extrabold text-white">How The Dormitory Management System Operates</h3>
            <p class="text-sm text-slate-400 mt-3">From verified student pre-registration to ongoing resident safety and welfare support.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
            <!-- Step 1 -->
            <div class="p-6 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] hover:border-emerald-500/30 transition-all space-y-4 shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-xl bg-[#0B101D] border border-white/10 text-emerald-400 font-bold text-base flex items-center justify-center font-mono">
                        01
                    </span>
                    <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400">Admin Level</span>
                </div>
                <h4 class="text-base font-bold text-white">Student Pre-Registration</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Administrators upload or pre-register official student rosters with confirmed University IDs, department codes, and gender.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="p-6 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] hover:border-emerald-500/30 transition-all space-y-4 shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-xl bg-[#0B101D] border border-white/10 text-emerald-400 font-bold text-base flex items-center justify-center font-mono">
                        02
                    </span>
                    <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400">Manager Level</span>
                </div>
                <h4 class="text-base font-bold text-white">Block Assignment</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Proctor Managers assign halls to qualified block proctors with gender alignment and wing supervision responsibilities.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="p-6 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] hover:border-emerald-500/30 transition-all space-y-4 shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-xl bg-[#0B101D] border border-white/10 text-emerald-400 font-bold text-base flex items-center justify-center font-mono">
                        03
                    </span>
                    <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400">Proctor Level</span>
                </div>
                <h4 class="text-base font-bold text-white">Smart Room Allocation</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Proctors allocate students to rooms based on capacity, verifying active keys and room asset inventories during handover.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="p-6 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] hover:border-emerald-500/30 transition-all space-y-4 shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-xl bg-[#0B101D] border border-white/10 text-emerald-400 font-bold text-base flex items-center justify-center font-mono">
                        04
                    </span>
                    <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-400">Student Level</span>
                </div>
                <h4 class="text-base font-bold text-white">Residence &amp; Inquiries</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Students log in to access room details, track curfew attendance, request gate passes, and report maintenance issues.
                </p>
            </div>
        </div>
    </section>

    <!-- Optional Announcements / University Notices (Smooth Scroll Target) -->
    @if(isset($announcements) && $announcements->count() > 0)
        <section id="announcements" class="py-20 border-t border-white/[0.06] bg-[#0E1420]/30 scroll-mt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-widest text-emerald-400 mb-2">Campus Directives</h2>
                        <h3 class="text-3xl font-extrabold text-white">Official Residence Notices</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($announcements as $post)
                        <div class="p-6 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] hover:border-emerald-500/30 transition-all flex flex-col justify-between shadow-xl shadow-black/20">
                            <div>
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-medium">
                                        {{ $post->category ?? 'Notice' }}
                                    </span>
                                    <span>{{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}</span>
                                </div>
                                <h4 class="text-lg font-bold text-white mb-2">{{ $post->title }}</h4>
                                <p class="text-xs sm:text-sm text-slate-400 line-clamp-3 leading-relaxed">
                                    {{ $post->summary ?? Str::limit($post->content, 120) }}
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-white/[0.06] text-xs text-slate-400 flex items-center justify-between">
                                <span>Residence Directorate</span>
                                <span class="text-emerald-400 font-semibold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Active Directive
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Contact & Inquiry Submission Section (Includes required 'id="contact"', 'Contact Dormitory Directorate', Smooth Scroll Target) -->
    <section id="contact" class="py-24 border-t border-white/[0.06] bg-[#0E1420]/50 scroll-mt-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold uppercase tracking-wider text-emerald-400 mb-3">
                    <span>Direct Communication</span>
                </div>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-white">Contact Dormitory Directorate &amp; Student Affairs</h3>
                <p class="text-sm text-slate-400 mt-3">
                    Need assistance regarding bed placement, pre-registration verification, or special accommodation? Reach out directly to campus residence staff.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Contact Information Cards -->
                <div class="space-y-4">
                    <div class="p-6 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] flex items-start gap-4 shadow-xl">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Main Campus Office</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                Woldia University Main Campus<br />
                                Directorate of Student Service &amp; Residence Life<br />
                                Woldia, Amhara Region, Ethiopia
                            </p>
                        </div>
                    </div>

                    <div class="p-6 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] flex items-start gap-4 shadow-xl">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Telephone &amp; Hotline</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                Office Desk: +251 33 331 2285<br />
                                Proctor Duty Office: +251 33 331 1140<br />
                                Hours: Mon &ndash; Fri, 8:30 AM &ndash; 5:30 PM
                            </p>
                        </div>
                    </div>

                    <div class="p-6 rounded-3xl bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] flex items-start gap-4 shadow-xl">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Official Communications</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                Dormitory Services: <a href="mailto:dormitory@wldu.edu.et" class="text-emerald-400 hover:underline">dormitory@wldu.edu.et</a><br />
                                Student Inquiries: <a href="mailto:support@wldu.edu.et" class="text-emerald-400 hover:underline">support@wldu.edu.et</a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact & Inquiry Form -->
                <div class="lg:col-span-2 bg-[#111726]/80 backdrop-blur-md border border-white/[0.07] shadow-xl rounded-3xl p-6 sm:p-8">
                    <h4 class="text-lg font-bold text-white mb-1.5">Submit an Accommodation Inquiry</h4>
                    <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                        Have an inquiry regarding room placement, medical accommodation, or pre-registration verification? Send a message to the residence life team.
                    </p>

                    <!-- Flash Session Success Feedback -->
                    @if (session('success'))
                        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-300 flex items-center gap-3 mb-6 shadow-md backdrop-blur-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)] shrink-0"></span>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Official Contact Form -->
                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="contact-name" class="block text-[10px] uppercase font-semibold tracking-wider text-slate-300 mb-1.5">
                                    Full Name <span class="text-emerald-400">*</span>
                                </label>
                                <input id="contact-name" 
                                       name="name" 
                                       type="text" 
                                       value="{{ old('name') }}" 
                                       required 
                                       placeholder="e.g. Abebe Bikila" 
                                       class="w-full bg-[#0B101D] border border-white/10 text-white placeholder-slate-500 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50 transition-all" />
                                @error('name')
                                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="contact-uid" class="block text-[10px] uppercase font-semibold tracking-wider text-slate-300 mb-1.5">
                                    University ID / Roster Number
                                </label>
                                <input id="contact-uid" 
                                       name="university_id" 
                                       type="text" 
                                       value="{{ old('university_id') }}" 
                                       placeholder="e.g. WLD/0101/14 or 1002" 
                                       class="w-full bg-[#0B101D] border border-white/10 text-white placeholder-slate-500 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50 transition-all" />
                                @error('university_id')
                                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="contact-email" class="block text-[10px] uppercase font-semibold tracking-wider text-slate-300 mb-1.5">
                                    Email Address <span class="text-emerald-400">*</span>
                                </label>
                                <input id="contact-email" 
                                       name="email" 
                                       type="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       placeholder="name@wldu.edu.et" 
                                       class="w-full bg-[#0B101D] border border-white/10 text-white placeholder-slate-500 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50 transition-all" />
                                @error('email')
                                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="contact-topic" class="block text-[10px] uppercase font-semibold tracking-wider text-slate-300 mb-1.5">
                                    Inquiry Category
                                </label>
                                <select id="contact-topic" 
                                        name="topic" 
                                        class="w-full bg-[#0B101D] border border-white/10 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50 transition-all">
                                    <option value="Room Allocation" @selected(old('topic') == 'Room Allocation')>Room Allocation &amp; Bed Placement</option>
                                    <option value="Pre-Registration" @selected(old('topic') == 'Pre-Registration')>Account Pre-Registration</option>
                                    <option value="Special Accommodation" @selected(old('topic') == 'Special Accommodation')>Special Needs &amp; Medical Accommodation</option>
                                    <option value="Maintenance" @selected(old('topic') == 'Maintenance')>Facility &amp; Water Maintenance</option>
                                    <option value="General" @selected(old('topic') == 'General')>General Residence Life Inquiry</option>
                                </select>
                                @error('topic')
                                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="contact-message" class="block text-[10px] uppercase font-semibold tracking-wider text-slate-300 mb-1.5">
                                Detailed Message <span class="text-emerald-400">*</span>
                            </label>
                            <textarea id="contact-message" 
                                      name="message" 
                                      rows="4" 
                                      required 
                                      placeholder="Provide full details regarding your dormitory accommodation inquiry..." 
                                      class="w-full bg-[#0B101D] border border-white/10 text-white placeholder-slate-500 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50 transition-all">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" 
                                    class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold shadow-lg shadow-emerald-500/20 transition-all rounded-xl px-6 py-3.5 flex items-center gap-2 active:scale-[0.98] text-sm">
                                <span>Send Inquiry</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Beautiful Modern Multi-Column Footer (Inspired by DormEase sample) -->
    <footer class="border-t border-white/[0.06] bg-[#0A0E17] pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8 pb-12 border-b border-white/[0.06]">
                <!-- Brand & Mission Column (Spans 2 columns on lg) -->
                <div class="lg:col-span-2 space-y-5">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.jpg') }}" 
                             alt="Woldia University Crest" 
                             class="w-10 h-10 object-contain rounded-xl bg-white/5 p-1 border border-white/10 shadow-md" />
                        <div>
                            <span class="text-base font-bold text-white block tracking-tight">Woldia University</span>
                            <span class="text-xs text-emerald-400 font-semibold uppercase tracking-wider block">Dormitory Management System</span>
                        </div>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-400 max-w-sm leading-relaxed">
                        The modern dormitory management system for Woldia University. Built for proctors, loved by residents. Simplify rooms, residents, and reporting in one unified dashboard.
                    </p>

                    <!-- Social Icons: Telegram, LinkedIn, Facebook -->
                    <div class="flex items-center gap-2.5 pt-1">
                        <!-- Telegram Icon -->
                        <a href="https://t.me/woldiauniversity" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           title="Join Woldia University on Telegram"
                           class="w-9 h-9 rounded-xl bg-white/[0.05] hover:bg-emerald-500/15 border border-white/10 hover:border-emerald-500/30 text-slate-300 hover:text-emerald-400 flex items-center justify-center transition-all">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.196 1.006.128.832.926z"/></svg>
                        </a>

                        <!-- LinkedIn Icon -->
                        <a href="https://linkedin.com/school/woldia-university" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           title="Connect on LinkedIn"
                           class="w-9 h-9 rounded-xl bg-white/[0.05] hover:bg-emerald-500/15 border border-white/10 hover:border-emerald-500/30 text-slate-300 hover:text-emerald-400 flex items-center justify-center transition-all">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>

                        <!-- Facebook Icon -->
                        <a href="https://facebook.com/WoldiaUniversityOfficial" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           title="Follow on Facebook"
                           class="w-9 h-9 rounded-xl bg-white/[0.05] hover:bg-emerald-500/15 border border-white/10 hover:border-emerald-500/30 text-slate-300 hover:text-emerald-400 flex items-center justify-center transition-all">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Product / Platform Navigation -->
                <div class="space-y-4">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-white">Platform</h5>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li><a href="#features" class="hover:text-emerald-400 transition-colors">Core Features</a></li>
                        <li><a href="#stats" class="hover:text-emerald-400 transition-colors">System Metrics</a></li>
                        <li><a href="#how-it-works" class="hover:text-emerald-400 transition-colors">How It Works</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition-colors">Student Resident Portal</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-emerald-400 transition-colors">Account Activation</a></li>
                    </ul>
                </div>

                <!-- Institution Navigation -->
                <div class="space-y-4">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-white">Institution</h5>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li><a href="#about" class="hover:text-emerald-400 transition-colors">About Woldia University</a></li>
                        <li><a href="#about" class="hover:text-emerald-400 transition-colors">Campus History (2011)</a></li>
                        <li><a href="#contact" class="hover:text-emerald-400 transition-colors">Directorate Office</a></li>
                        <li><a href="#contact" class="hover:text-emerald-400 transition-colors">Proctor Emergency Hotline</a></li>
                        @if(isset($announcements) && $announcements->count() > 0)
                            <li><a href="#announcements" class="hover:text-emerald-400 transition-colors">Official Directives</a></li>
                        @endif
                    </ul>
                </div>

                <!-- Legal, Terms & Privacy -->
                <div class="space-y-4">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-white">Legal &amp; Policy</h5>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li>
                            <button type="button" 
                                    @click="showPolicyModal = true; policyTab = 'terms'" 
                                    class="text-left hover:text-emerald-400 transition-colors focus:outline-none">
                                Terms of Service
                            </button>
                        </li>
                        <li>
                            <button type="button" 
                                    @click="showPolicyModal = true; policyTab = 'privacy'" 
                                    class="text-left hover:text-emerald-400 transition-colors focus:outline-none">
                                Privacy Policy
                            </button>
                        </li>
                        <li>
                            <button type="button" 
                                    @click="showPolicyModal = true; policyTab = 'terms'" 
                                    class="text-left hover:text-emerald-400 transition-colors focus:outline-none">
                                Gender Zoning Rules
                            </button>
                        </li>
                        <li>
                            <button type="button" 
                                    @click="showPolicyModal = true; policyTab = 'terms'" 
                                    class="text-left hover:text-emerald-400 transition-colors focus:outline-none">
                                Curfew Guidelines
                            </button>
                        </li>
                        <li>
                            <button type="button" 
                                    @click="showPolicyModal = true; policyTab = 'privacy'" 
                                    class="text-left hover:text-emerald-400 transition-colors focus:outline-none">
                                Student Records Protection
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Sub-Footer Bar -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div>
                    &copy; {{ date('Y') }} Woldia University Dormitory System. All rights reserved.
                </div>
                <div class="flex items-center gap-1.5">
                    <span>Built with</span>
                    <span class="text-rose-500">&hearts;</span>
                    <span>for better dormitory living.</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Terms of Service & Privacy Policy Modal -->
    <div x-show="showPolicyModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showPolicyModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-md"
         style="display: none;">
        
        <div @click.away="showPolicyModal = false"
             x-show="showPolicyModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="bg-[#111726]/95 backdrop-blur-2xl border border-white/10 rounded-3xl p-6 sm:p-8 max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl relative">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between pb-5  border-white/[0.08]">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Woldia University" class="w-10 h-10 object-contain rounded-xl  border-white/10 bg-white/5 p-1" />
                    <div>
                        <h3 class="text-lg font-bold text-white tracking-tight">Institutional Policy &amp; Legal Governance</h3>
                        <p class="text-xs text-slate-400">Directorate of Student Service &amp; Residence Life &bull; Woldia University</p>
                    </div>
                </div>

                <!-- Close Button -->
                <button type="button" 
                        @click="showPolicyModal = false" 
                        class="text-slate-400 hover:text-white p-1.5 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Tab Switcher -->
            <div class="flex items-center gap-2 pt-4 pb-3">
                <button type="button" 
                        @click="policyTab = 'terms'" 
                        :class="policyTab === 'terms' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'text-slate-400 hover:text-white border-transparent hover:bg-white/[0.04]'"
                        class="px-4 py-2 rounded-xl text-xs font-semibold border transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Terms of Service</span>
                </button>

                <button type="button" 
                        @click="policyTab = 'privacy'" 
                        :class="policyTab === 'privacy' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'text-slate-400 hover:text-white border-transparent hover:bg-white/[0.04]'"
                        class="px-4 py-2 rounded-xl text-xs font-semibold border transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Privacy Policy</span>
                </button>
            </div>

            <!-- Scrollable Content Body -->
            <div class="flex-1 overflow-y-auto pr-2 space-y-5 text-xs text-slate-300 leading-relaxed custom-sidebar-scroll py-2">
                <!-- 1. Terms of Service View -->
                <div x-show="policyTab === 'terms'" class="space-y-4">
                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <h4 class="text-sm font-bold text-white mb-1.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            1. Residential Eligibility &amp; Registration
                        </h4>
                        <p class="text-slate-400">
                            Only currently matriculated Woldia University undergraduate and postgraduate students with verified Registrar University IDs are eligible for dormitory room allocation. Accounts must be claimed and registered with accurate contact records. Subletting or transferring assigned beds to unregistered individuals is strictly prohibited.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <h4 class="text-sm font-bold text-white mb-1.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            2. Mandatory Gender Segregation
                        </h4>
                        <p class="text-slate-400">
                            In accordance with university charter and campus residential code, male and female dormitory blocks are strictly segregated. Cross-gender entry into residential wings is strictly prohibited without explicit, written proctor directorate clearance.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <h4 class="text-sm font-bold text-white mb-1.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            3. Curfew Hours &amp; Gate Passes
                        </h4>
                        <p class="text-slate-400">
                            Nightly roll call occurs between 9:00 PM and 10:00 PM. All resident students are required to be present in their allocated rooms during proctor verification. Students requiring absence during curfew hours must submit an official digital Gate Pass request at least 12 hours prior to scheduled departure.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <h4 class="text-sm font-bold text-white mb-1.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            4. Room Assets &amp; Property Clearance
                        </h4>
                        <p class="text-slate-400">
                            Each resident is personally responsible for assigned dormitory property, including beds, mattresses, study tables, and room keys. At the conclusion of each academic term, students must complete formal asset clearance with their block proctor before graduation or semester progression credentials are released.
                        </p>
                    </div>
                </div>

                <!-- 2. Privacy Policy View -->
                <div x-show="policyTab === 'privacy'" class="space-y-4" style="display: none;">
                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <h4 class="text-sm font-bold text-white mb-1.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            1. Student Information Collection
                        </h4>
                        <p class="text-slate-400">
                            The Dormitory Management System processes student personal information, including University IDs, legal names, institutional emails, enrolled department, batch year, and demographic gender solely for the purpose of dormitory zoning, bed allocation, and student welfare administration.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <h4 class="text-sm font-bold text-white mb-1.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            2. Attendance &amp; Security Log Retention
                        </h4>
                        <p class="text-slate-400">
                            Nightly roll call attendance logs, gate pass authorizations, and curfew absence records are retained securely during the academic year to ensure campus safety, audit compliance, and parental or emergency notification when authorized by university leadership.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <h4 class="text-sm font-bold text-white mb-1.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            3. Role-Based Scoped Access Controls
                        </h4>
                        <p class="text-slate-400">
                            Access to student residential records is strictly restricted to assigned hall proctors, proctor managers, and university system administrators. No resident data is shared with third parties or external commercial entities without explicit student authorization.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <h4 class="text-sm font-bold text-white mb-1.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            4. Credential Security &amp; Audit Logs
                        </h4>
                        <p class="text-slate-400">
                            All passwords and security credentials are encrypted with modern industry-standard cryptographic algorithms (Bcrypt/Argon2). Immutable audit trails record critical administrative actions to preserve institutional accountability and safeguard system integrity.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-4 border-t border-white/[0.08] flex items-center justify-between text-[11px] text-slate-500">
                <span>Effective Academic Year 2026 &bull; Woldia University</span>
                <button type="button" 
                        @click="showPolicyModal = false" 
                        class="px-4 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-white font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
</x-guest-layout>
