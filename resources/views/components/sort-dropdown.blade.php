@props([
    'column',
    'label' => 'Order',
    'ascLabel' => 'Ascending (A → Z)',
    'descLabel' => 'Descending (Z → A)',
    'align' => 'left',
])

@php
    $currentSort = request('sort');
    $currentDirection = strtolower(request('direction', 'asc'));
    $isActive = $currentSort === $column;
    $isAsc = $isActive && $currentDirection === 'asc';
    $isDesc = $isActive && $currentDirection === 'desc';

    $ascUrl = request()->fullUrlWithQuery(['sort' => $column, 'direction' => 'asc', 'page' => 1]);
    $descUrl = request()->fullUrlWithQuery(['sort' => $column, 'direction' => 'desc', 'page' => 1]);
    $resetUrl = request()->fullUrlWithQuery(['sort' => null, 'direction' => null, 'page' => 1]);
@endphp

<div class="inline-block relative ml-1.5 align-middle normal-case tracking-normal"
     x-data="{
         open: false,
         align: '{{ $align }}',
         top: 0,
         left: 0,
         updatePosition() {
             if (!this.$refs.trigger) return;
             const rect = this.$refs.trigger.getBoundingClientRect();
             if (rect.width === 0 && rect.height === 0) return;
             const menuWidth = 208;
             const measuredHeight = this.$refs.menu ? this.$refs.menu.offsetHeight : 0;
             const menuHeight = measuredHeight > 0 ? measuredHeight : 165;
             const gap = 6;
             let left = this.align === 'right' ? rect.right - menuWidth : rect.left;
             left = Math.max(12, Math.min(left, window.innerWidth - menuWidth - 12));
             const spaceBelow = window.innerHeight - rect.bottom;
             const top = (spaceBelow >= menuHeight || spaceBelow >= rect.top)
                 ? rect.bottom + gap
                 : Math.max(12, rect.top - menuHeight - gap);
             this.top = Math.round(top);
             this.left = Math.round(left);
         },
         toggle() {
             if (this.open) {
                 this.open = false;
                 return;
             }
             this.updatePosition();
             this.open = true;
             this.$nextTick(() => this.updatePosition());
         }
     }"
     @keydown.escape.window="open = false"
     @scroll.capture.window="if (open) { const r = $refs.trigger.getBoundingClientRect(); if (r.bottom < 64 || r.top > window.innerHeight - 8) { open = false; } else { updatePosition(); } }"
     @resize.window="if (open) updatePosition()">
    <!-- 3-Dot Vertical Button -->
    <button x-ref="trigger"
            type="button"
            @click="toggle()"
            :aria-expanded="open.toString()"
            title="Sort by {{ $label }}"
            class="inline-flex items-center justify-center p-1 rounded-lg transition-all duration-150 focus:outline-none {{ $isActive ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/[0.05]' }}">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
            <circle cx="12" cy="5" r="2"/>
            <circle cx="12" cy="12" r="2"/>
            <circle cx="12" cy="19" r="2"/>
        </svg>
        @if($isActive)
            <span class="sr-only">Sorted: {{ $isAsc ? 'Ascending' : 'Descending' }}</span>
        @endif
    </button>

    <!-- Teleported Dropdown Menu (never clipped by table overflow or card blur layers) -->
    <template x-teleport="body">
        <div x-show="open"
             x-cloak
             x-ref="menu"
             @click.outside="if (!$refs.trigger || !$refs.trigger.contains($event.target)) open = false"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             :style="{ position: 'fixed', top: top + 'px', left: left + 'px', zIndex: 45 }"
             class="w-52 rounded-xl bg-[#121927]/95 backdrop-blur-xl border border-white/[0.08] shadow-2xl py-1.5 text-xs divide-y divide-white/[0.06]"
             style="display: none;">

        <!-- Dropdown Header -->
        <div class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400 flex items-center justify-between">
            <span>Sort: {{ $label }}</span>
            @if($isActive)
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            @endif
        </div>

        <!-- Sort Options -->
        <div class="py-1">
            <!-- Ascending -->
            <a href="{{ $ascUrl }}"
               class="flex items-center justify-between px-3 py-2 text-xs transition-colors {{ $isAsc ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-200 hover:bg-white/[0.05] hover:text-white' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 {{ $isAsc ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                    </svg>
                    <span>{{ $ascLabel }}</span>
                </span>
                @if($isAsc)
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </a>

            <!-- Descending -->
            <a href="{{ $descUrl }}"
               class="flex items-center justify-between px-3 py-2 text-xs transition-colors {{ $isDesc ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-200 hover:bg-white/[0.05] hover:text-white' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 {{ $isDesc ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/>
                    </svg>
                    <span>{{ $descLabel }}</span>
                </span>
                @if($isDesc)
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </a>
        </div>

        @if($isActive)
            <!-- Reset Default -->
            <div class="py-1">
                <a href="{{ $resetUrl }}"
                   class="flex items-center gap-2 px-3 py-1.5 text-[11px] text-slate-400 hover:text-white hover:bg-white/[0.05] transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Reset to Default</span>
                </a>
            </div>
        @endif
        </div>
    </template>
</div>
