<x-app-layout title="Room Information & Guidelines">
    <div class="space-y-6">
        <!-- Header -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 mb-1 block">Living Standards & Residence Code</span>
            <h2 class="text-2xl font-bold tracking-tight text-white">Room Information & Housing Code</h2>
            <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">Official dormitory policies, living standards, and campus residence regulations for Woldia University.</p>
        </div>

        @if($allocation)
            <!-- Current Placement Snapshot -->
            <div class="p-6 rounded-3xl bg-[#172134]/60 border border-white/[0.05] flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-emerald-400 font-semibold uppercase tracking-wider block">Your Designated Residence</span>
                        <div class="text-base font-bold text-white mt-0.5">
                            {{ $allocation->room->block->name }} ({{ $allocation->room->block->block_code }}) &bull; Room #{{ $allocation->room->room_number }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-medium capitalize {{ $allocation->room->block->gender_category === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-pink-500/10 text-pink-400 border border-pink-500/20' }}">
                        {{ $allocation->room->block->gender_category }} Block
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/[0.05] text-white border border-white/10">
                        Max {{ $allocation->room->capacity }} Beds
                    </span>
                </div>
            </div>
        @endif

        <!-- Rules & Guidelines Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 1. Gender Separation & Visitors -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20 space-y-3">
                <div class="flex items-center gap-2.5 text-cyan-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">1. Gender Segregation & Access</h3>
                </div>
                <ul class="text-xs text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                    <li>Entry into opposite-gender dormitory blocks is strictly prohibited at all times.</li>
                    <li>External visitors are only permitted in designated reception lounges between 8:00 AM and 6:00 PM.</li>
                    <li>Overnight non-resident guests are strictly unauthorized and subject to disciplinary hearings.</li>
                    <li>Dormitory security guards verify student ID cards upon hall entry.</li>
                </ul>
            </div>

            <!-- 2. Quiet Hours & Conduct -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20 space-y-3">
                <div class="flex items-center gap-2.5 text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">2. Quiet Hours & Conduct</h3>
                </div>
                <ul class="text-xs text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                    <li>Mandatory quiet hours are observed from 10:00 PM to 6:00 AM every night.</li>
                    <li>Loud audio equipment, amplified instruments, and disruptive gatherings are forbidden in study hours.</li>
                    <li>Smoking, alcohol consumption, illicit substances, and hazardous materials are entirely prohibited on campus grounds.</li>
                    <li>Respect fellow roommates' rest, academic focus, and personal property.</li>
                </ul>
            </div>

            <!-- 3. Room Maintenance & Cleanliness -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20 space-y-3">
                <div class="flex items-center gap-2.5 text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">3. Hygiene & Maintenance</h3>
                </div>
                <ul class="text-xs text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                    <li>Residents share collective responsibility for daily room sanitation, ventilation, and trash disposal.</li>
                    <li>Electric cooking coils and high-wattage unauthorized appliances are forbidden to prevent fire hazards.</li>
                    <li>Report broken fixtures, leaking taps, or electrical faults immediately via the system feedback portal.</li>
                    <li>Routine room inspections are conducted weekly by block proctors.</li>
                </ul>
            </div>

            <!-- 4. Key Custody & Check-Out -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20 space-y-3">
                <div class="flex items-center gap-2.5 text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">4. Key Custody & Clearance</h3>
                </div>
                <ul class="text-xs text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                    <li>Room keys are non-transferable and must never be duplicated or lent to third parties.</li>
                    <li>Lost keys must be reported immediately; lock replacement costs will be assessed to the resident.</li>
                    <li>At the end of each semester, official room clearance and key handover must be signed off by the proctor.</li>
                    <li>Vandalism or damage to university property results in academic clearance holds.</li>
                </ul>
            </div>
        </div>

        <!-- Emergency Support Banner -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
            <div>
                <h4 class="text-sm font-bold text-white">Campus Residence Emergency Contacts</h4>
                <p class="text-xs text-slate-400 mt-1">Need immediate assistance with health emergencies, security, or room maintenance?</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="px-3.5 py-2 rounded-xl bg-[#0F1420] border border-white/[0.08] text-xs">
                    <span class="text-slate-400">Proctor Hotline:</span>
                    <span class="text-emerald-400 font-bold ml-1 font-mono">ext. 4401</span>
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-[#0F1420] border border-white/[0.08] text-xs">
                    <span class="text-slate-400">Security Gate:</span>
                    <span class="text-cyan-400 font-bold ml-1 font-mono">ext. 4499</span>
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-[#0F1420] border border-white/[0.08] text-xs">
                    <span class="text-slate-400">Clinic Ambulatory:</span>
                    <span class="text-rose-400 font-bold ml-1 font-mono">ext. 911</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
