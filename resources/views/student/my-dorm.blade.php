<x-app-layout title="My Dormitory">
    <div class="space-y-6">
        <!-- Top Banner -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 mb-1 block">Campus Accommodation</span>
                <h2 class="text-2xl font-bold tracking-tight text-white">My Dormitory Allocation</h2>
                <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">Details regarding your allocated dormitory hall, room placement, and roommates.</p>
            </div>
            @if($allocation)
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Active Resident
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    Awaiting Placement
                </span>
            @endif
        </div>

        @if($allocation && $room && $block)
            <!-- Allocated Info Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Hall & Room Card with Image Banners -->
                <div class="lg:col-span-2 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20 overflow-hidden space-y-6">
                    <!-- Split Visual Banner: Block Image & Room Image -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 h-48 sm:h-56">
                        <!-- Block Image Banner -->
                        <div class="relative h-full overflow-hidden theme-preserve-dark">
                            <img src="{{ $block->image_url }}" alt="{{ $block->name }}" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-gradient-to-t from-[#121927] via-[#121927]/40 to-black/40"></div>
                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold text-white bg-black/60 border border-white/10 backdrop-blur-sm">{{ $block->block_code }}</span>
                            </div>
                            <div class="absolute bottom-3 left-4 right-4">
                                <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider block">Assigned Hall</span>
                                <h3 class="text-lg font-bold text-white tracking-tight drop-shadow">{{ $block->name }}</h3>
                            </div>
                        </div>

                        <!-- Room Image Banner -->
                        <div class="relative h-full overflow-hidden border-t sm:border-t-0 sm:border-l border-white/[0.06] theme-preserve-dark">
                            <img src="{{ $room->image_url }}" alt="Room {{ $room->room_number }}" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-gradient-to-t from-[#121927] via-[#121927]/40 to-black/40"></div>
                            <div class="absolute top-3 right-3">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium uppercase {{ $block->gender_category === 'male' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'bg-pink-500/20 text-pink-300 border border-pink-500/30' }} backdrop-blur-sm">{{ $block->gender_category }} Block</span>
                            </div>
                            <div class="absolute bottom-3 left-4 right-4">
                                <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider block">Assigned Room</span>
                                <h3 class="text-lg font-bold text-white tracking-tight drop-shadow">Room {{ $room->room_number }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 pt-0 space-y-6">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                                <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block">Room Capacity</span>
                                <span class="text-base font-bold text-white mt-1 block">{{ $room->capacity }} Beds</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                                <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block">Occupancy</span>
                                <span class="text-base font-bold text-white mt-1 block">{{ $room->activeAllocations()->count() }} / {{ $room->capacity }}</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                                <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block">Allocated Date</span>
                                <span class="text-base font-bold text-white mt-1 block">{{ $allocation->allocated_at ? $allocation->allocated_at->format('M d, Y') : 'N/A' }}</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05]">
                                <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block">Allocated By</span>
                                <span class="text-base font-bold text-white mt-1 block truncate">{{ $allocation->allocator?->name ?? 'Proctor Desk' }}</span>
                            </div>
                        </div>

                        @if($allocation->remarks)
                            <div class="p-4 rounded-2xl bg-[#0F1420] border border-white/[0.06] text-xs">
                                <span class="text-[10px] text-emerald-400 uppercase font-semibold tracking-wider block">Allocation Remarks</span>
                                <p class="text-slate-300 mt-1 leading-relaxed">{{ $allocation->remarks }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Student Profile Summary -->
                <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20 flex flex-col justify-between">
                    <div>
                        <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 block mb-3">Resident Identity</span>
                        <div class="flex items-center gap-3.5 mb-4">
                            <img class="w-12 h-12 rounded-xl object-cover border border-white/10" src="{{ $student->avatar_url }}" alt="{{ $student->name }}" />
                            <div>
                                <h4 class="text-sm font-bold text-white">{{ $student->name }}</h4>
                                <span class="text-xs font-mono text-slate-400">{{ $student->university_id }}</span>
                            </div>
                        </div>

                        <div class="space-y-3 text-xs border-t border-white/[0.06] pt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Gender:</span>
                                <span class="font-medium text-white capitalize">{{ $student->gender ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Academic Year:</span>
                                <span class="font-medium text-white">Year {{ $student->academic_year ?? '1' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Email:</span>
                                <span class="font-medium text-white truncate max-w-[150px]">{{ $student->email }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-5 border-t border-white/[0.06]">
                        <a href="{{ route('student.comments.create') }}" class="w-full py-2.5 px-4 rounded-xl text-xs font-medium text-center block bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 text-slate-300 hover:text-white transition-all">
                            Report Room Issue / Maintenance
                        </a>
                    </div>
                </div>
            </div>

            <!-- Roommates Card -->
            <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20 space-y-4">
                <div class="flex items-center justify-between border-b border-white/[0.06] pb-3">
                    <h3 class="text-base font-bold text-white">Your Roommates in Room #{{ $room->room_number }}</h3>
                    <span class="text-xs text-slate-400">{{ $roommates->count() }} Fellow Resident(s)</span>
                </div>

                @if($roommates->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($roommates as $mate)
                            <div class="p-4 rounded-2xl bg-[#172134]/60 border border-white/[0.05] flex items-center gap-3.5">
                                <img class="w-10 h-10 rounded-xl object-cover border border-white/10" src="{{ $mate->avatar_url }}" alt="{{ $mate->name }}" />
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-white truncate">{{ $mate->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $mate->university_id }} &bull; Year {{ $mate->academic_year ?? '1' }}</div>
                                    <div class="text-[10px] text-emerald-400 capitalize font-medium mt-0.5">{{ $mate->gender }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic py-2">No other roommates currently assigned to this room.</p>
                @endif
            </div>
        @else
            <!-- Not Allocated State -->
            <div class="p-8 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl text-center space-y-4 max-w-3xl mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto text-emerald-400 shadow-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">No Dormitory Placement Yet</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
                        Your university account is active (<span class="text-white font-medium capitalize">{{ $student->gender ?? 'Unspecified' }}</span>, Year {{ $student->academic_year ?? '1' }}), but a proctor has not yet assigned you to a room.
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-[#0F1420] border border-white/[0.06] text-xs text-left space-y-2">
                    <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider block">What to do next:</span>
                    <ul class="list-disc list-inside text-slate-400 space-y-1">
                        <li>Visit the Proctor Office for your academic faculty.</li>
                        <li>Ensure your university ID card and registration slip are in order.</li>
                        <li>Allocations are finalized during the scheduled move-in window.</li>
                    </ul>
                </div>
                <div class="pt-3">
                    <a href="{{ route('student.comments.create') }}" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 rounded-xl text-xs shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all inline-flex items-center gap-2">
                        Submit Inquiry to Proctor
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
