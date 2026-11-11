<template>
  <!-- 1. MAIN LARGE ZIGZAG GRAPH -->
  <div v-if="variant === 'main'" class="p-6 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-emerald-500/30 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="absolute -top-12 -right-12 w-48 h-48 bg-[#163824]/20 rounded-full blur-3xl pointer-events-none group-hover:bg-[#10B981]/10 transition-colors"></div>

    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4 relative z-10">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#06B6D4] animate-pulse"></span>
          <h3 class="text-base sm:text-lg font-black text-white tracking-tight">{{ title }}</h3>
        </div>
        <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5 leading-relaxed">{{ subtitle }}</p>

        <div v-if="value !== undefined && value !== null && value !== ''" class="mt-3 flex items-baseline gap-3">
          <span class="text-3xl sm:text-4xl font-black text-white tracking-tight font-mono">{{ displayValue }}</span>
          <span v-if="badge" class="text-xs font-bold text-[#10B981] bg-[#163824] px-2.5 py-0.5 rounded-md border border-[#10B981]/30">{{ badge }}</span>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-white/[0.04] border border-white/[0.06] text-slate-400 flex items-center gap-1.5 shadow-sm">
          <svg class="w-3.5 h-3.5 text-[#06B6D4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span>{{ dateRange || 'Last 7 Days' }}</span>
        </span>
      </div>
    </div>

    <div class="relative w-full h-[280px] sm:h-[310px]">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
    </div>

    <div v-if="resolvedLegendItems.length" class="mt-4 pt-3 border-t border-white/[0.06] flex flex-wrap items-center justify-between gap-3 text-xs">
      <div v-for="(item, idx) in resolvedLegendItems" :key="idx" class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: item.color }"></span>
        <span class="text-slate-400">{{ item.label }}</span>
        <span v-if="item.value !== undefined && item.value !== null && item.value !== ''" class="font-bold text-white font-mono">{{ item.value }}</span>
      </div>
    </div>
  </div>

  <!-- 2. MINI BAR CARD -->
  <div v-else-if="variant === 'mini-bar'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-cyan-500/30 transition-all duration-300 flex flex-col justify-between shadow-lg relative group">
    <div class="flex items-start justify-between gap-2 mb-1">
      <div>
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">{{ title }}</span>
        <span v-if="subtitle" class="text-[10px] text-slate-500 block mt-0.5">{{ subtitle }}</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span v-if="badge" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-950/70 text-amber-300 border border-amber-500/30 font-mono">{{ badge }}</span>
      </div>
    </div>

    <div class="flex items-baseline justify-between mb-2">
      <div class="text-2xl sm:text-3xl font-black text-white tracking-tight font-mono">{{ displayValue }}</div>
    </div>

    <div class="relative w-full h-[100px]">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
      <div v-if="!resolvedLabels.length" class="absolute inset-0 flex items-center justify-center text-[11px] text-slate-500 pointer-events-none">
        No blocks registered
      </div>
    </div>

    <div v-if="resolvedLegendItems.length" class="mt-2 pt-2 border-t border-white/[0.06] flex items-center justify-between text-[11px]">
      <div v-for="(item, idx) in resolvedLegendItems" :key="idx" class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-sm" :style="{ backgroundColor: item.color }"></span>
        <span class="text-slate-400">{{ item.label }}</span>
        <span v-if="item.value !== undefined && item.value !== null && item.value !== ''" class="font-bold text-white font-mono ml-0.5">{{ item.value }}</span>
      </div>
    </div>
  </div>

  <!-- 3. MINI ZIGZAG LINE CARD -->
  <div v-else-if="variant === 'mini-zigzag'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-cyan-500/30 transition-all duration-300 flex flex-col justify-between shadow-lg relative group">
    <div class="flex items-start justify-between gap-2 mb-1">
      <div>
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">{{ title }}</span>
        <span v-if="subtitle" class="text-[10px] text-slate-500 block mt-0.5">{{ subtitle }}</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span v-if="badge" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#163824] text-[#10B981] border border-[#10B981]/30 font-mono">{{ badge }}</span>
      </div>
    </div>

    <div class="flex items-baseline justify-between mb-2">
      <div class="text-2xl sm:text-3xl font-black text-white tracking-tight font-mono">{{ displayValue }}</div>
    </div>

    <div class="relative w-full h-[100px]">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
    </div>

    <div class="mt-2 pt-2 border-t border-white/[0.06] flex items-center justify-between text-[10px] text-slate-500 font-mono">
      <span>{{ resolvedLabels.length ? resolvedLabels[0] : '' }}</span>
      <span>{{ resolvedLabels.length ? resolvedLabels[resolvedLabels.length - 1] : '' }}</span>
    </div>
  </div>

  <!-- 4. DONUT WITH CENTER PERCENTAGE -->
  <div v-else-if="variant === 'donut-center'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-cyan-500/30 transition-all duration-300 flex flex-col justify-between shadow-lg relative group">
    <div class="mb-1">
      <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">{{ title }}</span>
      <span v-if="subtitle" class="text-[10px] text-slate-500 block mt-0.5">{{ subtitle }}</span>
    </div>

    <div class="relative w-full h-[130px] flex items-center justify-center">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
      <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
        <span class="text-2xl sm:text-3xl font-black text-white font-mono tracking-tight drop-shadow-sm">{{ (centerText !== null && centerText !== undefined && centerText !== '') ? centerText : '0%' }}</span>
        <span v-if="centerLabel" class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">{{ centerLabel }}</span>
      </div>
    </div>

    <div v-if="resolvedLegendItems.length" class="mt-2 pt-2 border-t border-white/[0.06] space-y-1.5 text-[11px]">
      <div v-for="(item, idx) in resolvedLegendItems" :key="idx" class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: item.color }"></span>
          <span class="text-slate-400">{{ item.label }}</span>
        </div>
        <span class="font-bold text-white font-mono">{{ item.value }}</span>
      </div>
    </div>
  </div>

  <!-- 5. SEMI-CIRCLE ARC GAUGE -->
  <div v-else-if="variant === 'semi-gauge'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-[#EC4899]/40 transition-all duration-300 flex flex-col justify-between shadow-lg relative group">
    <div class="mb-1">
      <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">{{ title }}</span>
      <span v-if="subtitle" class="text-[10px] text-slate-500 block mt-0.5">{{ subtitle }}</span>
    </div>

    <div class="relative w-full h-[115px] flex items-center justify-center">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
    </div>

    <div v-if="resolvedLegendItems.length" class="mt-2 pt-2 border-t border-white/[0.06] space-y-1.5 text-[11px]">
      <div v-for="(item, idx) in resolvedLegendItems" :key="idx" class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: item.color }"></span>
          <span class="text-slate-400">{{ item.label }}</span>
        </div>
        <span class="font-bold text-white font-mono">{{ item.value }}</span>
      </div>
    </div>
  </div>

  <!-- 6. DUAL BAR CHART -->
  <div v-else-if="variant === 'dual-bar'" class="p-6 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-cyan-500/30 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-base font-black text-white tracking-tight">{{ title }}</h3>
        <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">{{ subtitle }}</p>
      </div>

      <div class="flex items-center gap-4">
        <div v-if="resolvedDatasets.length" class="flex items-center gap-3 text-xs">
          <div v-for="(ds, idx) in resolvedDatasets" :key="idx" class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: ds.backgroundColor || (idx === 0 ? '#06B6D4' : '#10B981') }"></span>
            <span class="text-slate-400 text-[11px] font-semibold">{{ ds.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="relative w-full h-[240px] sm:h-[260px]">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
      <div v-if="!resolvedLabels.length" class="absolute inset-0 flex items-center justify-center text-xs text-slate-500 pointer-events-none">
        No dormitory blocks registered yet
      </div>
    </div>
  </div>

  <!-- 7. RADIAL GAUGE -->
  <div v-else-if="variant === 'radial-gauge'" class="p-6 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-cyan-500/30 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="flex items-center justify-end mb-2">
      <span class="text-xs font-bold px-3 py-1 rounded-xl bg-white/[0.04] border border-white/[0.06] text-[#06B6D4] flex items-center gap-1">
        <span>{{ yearLabel || currentYear }}</span>
      </span>
    </div>

    <div class="relative w-full h-[150px] flex items-center justify-center">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
      <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pt-2">
        <span class="text-3xl font-black text-white font-mono tracking-tight">{{ (centerText !== null && centerText !== undefined && centerText !== '') ? centerText : '0%' }}</span>
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ centerLabel || 'Occupancy' }}</span>
      </div>
    </div>

    <div v-if="subtitle" class="text-center text-xs text-slate-400 my-2 font-medium">
      {{ subtitle }}
    </div>

    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-white/[0.06]">
      <div class="p-2.5 rounded-xl bg-[#0B0F17]/80 border border-white/[0.06] flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-[#002D3B] text-[#06B6D4] flex items-center justify-center shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
          <span class="text-[10px] text-slate-400 block uppercase font-bold">Allocated</span>
          <span class="text-xs font-black text-white font-mono">{{ (value !== null && value !== undefined && value !== '') ? value : '0' }}</span>
        </div>
      </div>

      <div class="p-2.5 rounded-xl bg-[#0B0F17]/80 border border-white/[0.06] flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-[#163824] text-[#10B981] flex items-center justify-center shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
        </div>
        <div>
          <span class="text-[10px] text-slate-400 block uppercase font-bold">Capacity</span>
          <span class="text-xs font-black text-white font-mono">{{ capacityText || '0 Beds' }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- 8. SPARK LINE / SUPERVISION REPORT -->
  <div v-else-if="variant === 'spark-line'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-amber-500/40 transition-all duration-300 flex items-center justify-between shadow-xl relative overflow-hidden group">
    <div>
      <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">{{ title }}</span>
      <div class="mt-1">
        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-amber-950/80 text-amber-300 border border-amber-500/30 uppercase tracking-wider font-mono">{{ badge || ('YEAR ' + currentYear) }}</span>
      </div>
      <div v-if="subtitle" class="mt-2.5 flex items-center gap-1.5">
        <span class="text-[11px] font-bold text-[#10B981] flex items-center gap-0.5">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
          {{ subtitle }}
        </span>
      </div>
      <div class="text-2xl font-black text-white font-mono mt-0.5">{{ (value !== null && value !== undefined && value !== '') ? value : '0' }}</div>
    </div>

    <div class="relative w-[130px] h-[75px] shrink-0">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
    </div>
  </div>

  <!-- 9. DONUT STAT -->
  <div v-else-if="variant === 'donut-stat'" class="p-6 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-cyan-500/30 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="flex items-center justify-between mb-2">
      <div>
        <h3 class="text-sm font-bold text-white tracking-tight">{{ title }}</h3>
        <p v-if="subtitle" class="text-[11px] text-slate-400">{{ subtitle }}</p>
      </div>
    </div>

    <div class="flex items-center justify-between my-2">
      <div>
        <div class="text-3xl font-black text-white font-mono">{{ displayValue }}</div>
        <span class="text-xs text-slate-400 mt-0.5 block">{{ valueLabel || 'Total Placements' }}</span>
      </div>

      <div class="relative w-[110px] h-[110px] flex items-center justify-center shrink-0">
        <canvas ref="canvasRef" class="w-full h-full block"></canvas>
        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          <span class="text-lg font-black text-white font-mono">{{ (centerText !== null && centerText !== undefined && centerText !== '') ? centerText : '0%' }}</span>
          <span class="text-[8px] font-bold text-slate-400 uppercase">{{ centerLabel || 'Placed' }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- 10. AREA WAVE -->
  <div v-else-if="variant === 'area-wave'" class="p-6 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-[#A855F7]/40 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="flex items-center gap-2 mb-4">
      <button
        type="button"
        @click="activeWaveTab = 'allocations'"
        :class="activeWaveTab === 'allocations' ? 'bg-[#06B6D4] text-[#0D1117] font-black' : 'bg-white/[0.04] text-slate-400 hover:text-white'"
        class="px-3.5 py-1.5 rounded-xl text-xs uppercase tracking-wider transition-all shadow-sm font-semibold"
      >
        Allocations
      </button>
      <button
        type="button"
        @click="activeWaveTab = 'leaves'"
        :class="activeWaveTab === 'leaves' ? 'bg-[#06B6D4] text-[#0D1117] font-black' : 'bg-white/[0.04] text-slate-400 hover:text-white'"
        class="px-3.5 py-1.5 rounded-xl text-xs uppercase tracking-wider transition-all shadow-sm font-semibold"
      >
        Leaves
      </button>
      <button
        type="button"
        @click="activeWaveTab = 'attendance'"
        :class="activeWaveTab === 'attendance' ? 'bg-[#06B6D4] text-[#0D1117] font-black' : 'bg-white/[0.04] text-slate-400 hover:text-white'"
        class="px-3.5 py-1.5 rounded-xl text-xs uppercase tracking-wider transition-all shadow-sm font-semibold"
      >
        Attendance
      </button>
    </div>

    <div class="flex items-center gap-3 mb-3 p-3 rounded-xl bg-[#0B0F17]/80 border border-white/[0.06]">
      <div class="w-10 h-10 rounded-xl bg-[#2E1065] text-[#A855F7] flex items-center justify-center shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
      </div>
      <div>
        <span class="text-[10px] text-slate-400 block uppercase font-bold">{{ metricLabel || 'Total Campus Residents' }}</span>
        <div class="flex items-center gap-2">
          <span class="text-xl font-black text-white font-mono">{{ (value !== null && value !== undefined && value !== '') ? value : '0' }}</span>
          <span v-if="badge" class="text-[11px] font-bold text-[#10B981] flex items-center">
            {{ badge }}
          </span>
        </div>
      </div>
    </div>

    <div class="relative w-full h-[150px]">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
    </div>
  </div>

  <!-- 12. METRIC WITH SUB-METRICS -->
  <div v-else-if="variant === 'metric-sub'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-emerald-500/30 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ title }}</span>
    </div>

    <div class="flex items-center gap-3 my-2">
      <div class="text-3xl sm:text-4xl font-black text-white font-mono tracking-tight">{{ displayValue }}</div>
      <div class="flex items-center text-[#10B981]">
        <svg class="w-8 h-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 16">
          <path d="M1 12l6-7 5 5 11-9" />
        </svg>
      </div>
    </div>

    <div v-if="resolvedSubMetrics.length" class="grid grid-cols-3 gap-2 pt-3 mt-2 border-t border-white/[0.06] text-center">
      <div v-for="(sub, idx) in resolvedSubMetrics" :key="idx" class="px-1" :class="idx < resolvedSubMetrics.length - 1 ? 'border-r border-white/[0.06]' : ''">
        <span class="text-[10px] uppercase font-bold text-slate-400 block truncate">{{ sub.label }}</span>
        <span class="text-xs sm:text-sm font-black text-white font-mono mt-0.5 block">{{ sub.value }}</span>
      </div>
    </div>
  </div>

  <!-- 13. DONUT WITH LEGEND -->
  <div v-else-if="variant === 'donut-legend'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-cyan-500/30 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ title }}</span>
    </div>

    <div class="flex items-center justify-between gap-3 my-1">
      <div class="relative w-[110px] h-[110px] shrink-0">
        <canvas ref="canvasRef" class="w-full h-full block"></canvas>
      </div>

      <div class="flex-1 space-y-1.5 text-xs">
        <div v-for="(item, idx) in resolvedLegendItems" :key="idx" class="flex items-center justify-between">
          <div class="flex items-center gap-2 truncate">
            <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: item.color }"></span>
            <span class="text-slate-400 text-[11px] truncate">{{ item.label }}</span>
          </div>
          <span class="font-bold text-white font-mono text-xs ml-1">{{ item.value }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- 14. PROGRESS BARS -->
  <div v-else-if="variant === 'progress-bars'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-emerald-500/30 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ title }}</span>
    </div>

    <div v-if="resolvedLegendItems.length" class="flex items-center gap-3 mb-2 text-[10px]">
      <div v-for="(item, idx) in resolvedLegendItems" :key="idx" class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: item.color }"></span>
        <span class="text-slate-400">{{ item.label }}</span>
      </div>
    </div>

    <div class="space-y-3.5 my-1">
      <div v-for="(bar, idx) in resolvedProgressItems" :key="idx">
        <div class="flex items-center justify-between text-xs mb-1">
          <span class="text-slate-400 font-medium text-[11px]">{{ bar.label }}</span>
          <span class="font-bold text-white font-mono text-[11px]">{{ bar.value }}%</span>
        </div>
        <div class="w-full h-2 rounded-full bg-[#0B0F17]/80 border border-white/[0.06] overflow-hidden flex">
          <div
            class="h-full rounded-full transition-all duration-700"
            :style="{
              width: bar.value + '%',
              background: bar.gradient || `linear-gradient(90deg, ${bar.color || '#06B6D4'}, ${bar.secondaryColor || '#10B981'})`
            }"
          ></div>
        </div>
      </div>
    </div>
  </div>

  <!-- 15. SPARKLINE ROWS -->
  <div v-else-if="variant === 'sparkline-rows'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-[#EC4899]/40 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ title }}</span>
    </div>

    <div class="space-y-3 my-1">
      <div v-for="(row, idx) in resolvedSparklineRows" :key="idx" class="flex items-center justify-between gap-3 p-2 rounded-xl bg-[#0B0F17]/80/60 border border-white/[0.06]">
        <div class="text-[11px] font-mono text-slate-400 truncate">
          <span class="font-bold text-white">{{ row.name }}</span>
          <span class="text-slate-500 ml-1">{{ row.metric }}</span>
        </div>
        <div class="w-[75px] h-[26px] shrink-0">
          <svg class="w-full h-full" viewBox="0 0 75 26" fill="none">
            <path :d="row.svgPath" :stroke="row.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- 16. DAILY MULTI LINE CHART -->
  <div v-else-if="variant === 'daily-multi-line'" class="p-6 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-emerald-500/30 transition-all duration-300 flex flex-col justify-between shadow-xl relative overflow-hidden group">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B] animate-pulse"></span>
          <h3 class="text-base sm:text-lg font-black text-white tracking-tight">{{ title }}</h3>
        </div>
        <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">{{ subtitle }}</p>
      </div>

      <div class="flex items-center gap-4">
        <div v-if="resolvedLegendItems.length" class="flex items-center gap-3 text-xs">
          <div v-for="(item, idx) in resolvedLegendItems" :key="idx" class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: item.color }"></span>
            <span class="text-slate-400 text-[11px] font-semibold">{{ item.label }}</span>
          </div>
        </div>

        <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-white/[0.04] border border-white/[0.06] text-slate-400 flex items-center gap-1.5 shadow-sm">
          <span>{{ dropdownText || 'Last 30 Days' }}</span>
        </span>
      </div>
    </div>

    <div class="relative w-full h-[280px] sm:h-[300px]">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
    </div>
  </div>

  <!-- 17. STAT WITH MINI BAR -->
  <div v-else-if="variant === 'stat-mini-bar'" class="p-5 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-cyan-500/30 transition-all duration-300 flex flex-col justify-between shadow-lg relative group">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ title }}</span>
      <span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-white/[0.04] border border-white/[0.06] text-slate-400 flex items-center gap-1">
        <span>{{ dropdownText || 'Last 7 Days' }}</span>
      </span>
    </div>

    <div class="flex items-end justify-between mt-2">
      <div>
        <div class="text-3xl font-black text-white font-mono tracking-tight">{{ displayValue }}</div>
        <span v-if="subtitle" class="text-[10px] text-slate-500 block mt-0.5">{{ subtitle }}</span>
      </div>

      <div class="relative w-[110px] h-[55px] shrink-0">
        <canvas ref="canvasRef" class="w-full h-full block"></canvas>
      </div>
    </div>
  </div>

  <!-- 11. STANDARD / FALLBACK CHART -->
  <div v-else class="p-6 rounded-2xl bg-[#131A29]/70 backdrop-blur-md border border-white/[0.06] hover:border-emerald-500/30 transition-all duration-300 flex flex-col justify-between shadow-lg relative group">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-sm font-bold text-white tracking-tight">{{ title }}</h3>
        <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">{{ subtitle }}</p>
      </div>
    </div>
    <div class="relative w-full h-[240px]">
      <canvas ref="canvasRef" class="w-full h-full block"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const props = defineProps({
  variant: { type: String, default: 'main' },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  value: { type: [String, Number], default: null },
  badge: { type: String, default: '' },
  dateRange: { type: String, default: '' },
  centerText: { type: String, default: '' },
  centerLabel: { type: String, default: '' },
  yearLabel: { type: String, default: '' },
  valueLabel: { type: String, default: '' },
  metricLabel: { type: String, default: '' },
  legendItems: { type: [Array, String], default: () => [] },
  type: { type: String, default: 'line' },
  isZigzag: { type: [Boolean, String], default: true },
  labels: { type: [Array, String], default: () => [] },
  datasets: { type: [Array, String], default: () => [] },
  data: { type: [Array, String], default: () => [] },
  waveTabsData: { type: [Object, String], default: () => ({}) },
  colors: { type: [Array, String], default: () => [] },
  label: { type: String, default: 'Count' },
  subMetrics: { type: [Array, String], default: () => [] },
  progressItems: { type: [Array, String], default: () => [] },
  sparklineRows: { type: [Array, String], default: () => [] },
  dropdownText: { type: String, default: '' },
  secondaryData: { type: [Array, String], default: () => [] },
  capacityText: { type: String, default: '' },
});

const currentYear = String(new Date().getFullYear());
const canvasRef = ref(null);
let chartInstance = null;
const activeWaveTab = ref('allocations');

function toArray(val, fallback = []) {
  if (Array.isArray(val)) return val;
  if (typeof val === 'string') {
    try {
      const p = JSON.parse(val);
      if (Array.isArray(p)) return p;
    } catch (_) {}
  }
  return fallback;
}

function toObject(val, fallback = {}) {
  if (typeof val === 'object' && val !== null) return val;
  if (typeof val === 'string') {
    try {
      return JSON.parse(val);
    } catch (_) {}
  }
  return fallback;
}

const resolvedLabels = computed(() => toArray(props.labels, []));
const resolvedData = computed(() => toArray(props.data, []));
const resolvedDatasets = computed(() => toArray(props.datasets, []));
const resolvedColors = computed(() => toArray(props.colors, []));
const resolvedLegendItems = computed(() => toArray(props.legendItems, []));
const resolvedWaveTabs = computed(() => toObject(props.waveTabsData, {}));
const resolvedSubMetrics = computed(() => toArray(props.subMetrics, []));
const resolvedProgressItems = computed(() => toArray(props.progressItems, []));
const resolvedSparklineRows = computed(() => toArray(props.sparklineRows, []));
const resolvedSecondaryData = computed(() => toArray(props.secondaryData, []));

const currentNumeric = ref(0);
const preText = ref('');
const sufText = ref('');
const isNonNum = ref(false);

const displayValue = computed(() => {
  if (props.value === null || props.value === undefined || props.value === '') return '';
  if (isNonNum.value) return String(props.value);
  return `${preText.value}${currentNumeric.value.toLocaleString()}${sufText.value}`;
});

function animateNumber() {
  if (props.value === null || props.value === undefined || props.value === '') return;
  const str = String(props.value).trim().replace(/,/g, '');
  const match = str.match(/^([^\d\-+]*)([-+]?[0-9]+(?:\.[0-9]+)?)(.*)$/);
  if (!match) {
    isNonNum.value = true;
    return;
  }
  isNonNum.value = false;
  preText.value = match[1];
  sufText.value = match[3];
  const target = parseFloat(match[2]);
  const start = 0;
  const duration = 650;
  const startTime = performance.now();

  function tick(now) {
    const elapsed = now - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const ease = 1 - Math.pow(1 - progress, 4);
    currentNumeric.value = Math.round(start + (target - start) * ease);
    if (progress < 1) {
      requestAnimationFrame(tick);
    } else {
      currentNumeric.value = target;
    }
  }
  requestAnimationFrame(tick);
}

function hexToRgba(hex, alpha = 0.2) {
  if (!hex || typeof hex !== 'string') return `rgba(0, 240, 255, ${alpha})`;
  let clean = hex.replace('#', '');
  if (clean.length === 3) {
    clean = clean.split('').map(c => c + c).join('');
  }
  const r = parseInt(clean.substring(0, 2), 16) || 0;
  const g = parseInt(clean.substring(2, 4), 16) || 240;
  const b = parseInt(clean.substring(4, 6), 16) || 255;
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

const renderChart = () => {
  if (!canvasRef.value) return;
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }

  const ctx = canvasRef.value.getContext('2d');
  if (!ctx) return;

  const isLight = typeof document !== 'undefined' && document.documentElement.classList.contains('light');
  const chartGridColor = isLight ? '#E2E8F0' : '#21262D';
  const chartTickColor = isLight ? '#334155' : '#9CA3AF';
  const chartSurfaceColor = isLight ? '#FFFFFF' : '#161B22';
  const chartTrackColor = isLight ? '#E2E8F0' : '#21262D';
  const tooltipBgColor = isLight ? 'rgba(255, 255, 255, 0.98)' : 'rgba(22, 27, 34, 0.96)';
  const tooltipTitleColor = isLight ? '#0F172A' : '#FFFFFF';
  const tooltipBodyColor = isLight ? '#334155' : '#E5E7EB';
  const tooltipBorderColor = isLight ? '#CBD5E1' : '#30363D';

  const baseTooltipStyle = {
    backgroundColor: tooltipBgColor,
    titleColor: tooltipTitleColor,
    bodyColor: tooltipBodyColor,
    borderColor: tooltipBorderColor,
    borderWidth: 1,
    padding: 8,
    cornerRadius: 8,
  };

  // 1. VARIANT: MAIN LARGE ZIGZAG GRAPH
  if (props.variant === 'main') {
    const defaultColors = ['#06B6D4', '#10B981', '#A855F7', '#F59E0B'];
    let preparedDatasets = [];

    const datasetsList = resolvedDatasets.value;
    if (datasetsList.length > 0) {
      preparedDatasets = datasetsList.map((ds, i) => {
        const color = ds.borderColor || defaultColors[i % defaultColors.length];
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, hexToRgba(color, 0.22));
        gradient.addColorStop(1, hexToRgba(color, 0.0));

        return {
          ...ds,
          borderColor: color,
          backgroundColor: gradient,
          fill: true,
          tension: 0,
          borderWidth: 2.5,
          pointRadius: 4.5,
          pointHoverRadius: 8,
          pointHitRadius: 15,
          pointBackgroundColor: color,
          pointBorderColor: chartSurfaceColor,
          pointBorderWidth: 2,
          pointHoverBackgroundColor: '#FFFFFF',
          pointHoverBorderColor: color,
          pointHoverBorderWidth: 3,
        };
      });
    } else {
      const color = '#06B6D4';
      const gradient = ctx.createLinearGradient(0, 0, 0, 260);
      gradient.addColorStop(0, hexToRgba(color, 0.25));
      gradient.addColorStop(1, hexToRgba(color, 0.0));

      preparedDatasets = [{
        label: props.label,
        data: resolvedData.value,
        borderColor: color,
        backgroundColor: gradient,
        fill: true,
        tension: 0,
        borderWidth: 2.5,
        pointRadius: 4.5,
        pointHoverRadius: 8,
      }];
    }

    chartInstance = new Chart(ctx, {
      type: 'line',
      data: { labels: resolvedLabels.value, datasets: preparedDatasets },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 750, easing: 'easeOutQuart' },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            ...baseTooltipStyle,
            padding: 12,
            cornerRadius: 10,
          },
        },
        scales: {
          x: { grid: { color: chartGridColor, drawBorder: false }, ticks: { color: chartTickColor } },
          y: { beginAtZero: true, suggestedMax: 4, grid: { color: chartGridColor, drawBorder: false }, ticks: { color: chartTickColor, precision: 0 } },
        },
      },
    });
    return;
  }

  // 2. VARIANT: DUAL BAR CHART
  if (props.variant === 'dual-bar') {
    chartInstance = new Chart(ctx, {
      type: 'bar',
      data: { labels: resolvedLabels.value, datasets: resolvedDatasets.value },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 750, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: {
            ...baseTooltipStyle,
            bodyColor: isLight ? '#0E7490' : '#06B6D4',
            padding: 10,
          },
        },
        scales: {
          x: { grid: { color: chartGridColor, drawBorder: false }, ticks: { color: chartTickColor, font: { family: 'Figtree', size: 10, weight: 600 } } },
          y: { beginAtZero: true, suggestedMax: 5, grid: { color: chartGridColor, drawBorder: false }, ticks: { color: chartTickColor, precision: 0 } },
        },
      },
    });
    return;
  }

  // 3. VARIANT: RADIAL GAUGE
  if (props.variant === 'radial-gauge') {
    const rawPct = parseFloat(
      (props.centerText !== null && props.centerText !== undefined && props.centerText !== '')
        ? props.centerText
        : (props.value ?? '0')
    );
    const pct = Number.isFinite(rawPct) ? Math.min(100, Math.max(0, rawPct)) : 0;

    chartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Occupied', 'Remaining'],
        datasets: [{
          data: [pct, Math.max(0, 100 - pct)],
          backgroundColor: ['#06B6D4', chartTrackColor],
          borderColor: chartSurfaceColor,
          borderWidth: 4,
          circumference: 240,
          rotation: -120,
          cutout: '78%',
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 800, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: { enabled: false },
        },
      },
    });
    return;
  }

  // 4. VARIANT: SPARK LINE / SUPERVISION REPORT
  if (props.variant === 'spark-line') {
    const sparkData = resolvedData.value;
    const sparkLabels = resolvedLabels.value.length ? resolvedLabels.value : sparkData.map((_, i) => String(i + 1));
    const gradient = ctx.createLinearGradient(0, 0, 0, 75);
    gradient.addColorStop(0, 'rgba(245, 158, 11, 0.35)');
    gradient.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

    chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: sparkLabels,
        datasets: [{
          data: sparkData,
          borderColor: '#F59E0B',
          backgroundColor: gradient,
          fill: true,
          tension: 0.4,
          borderWidth: 2.5,
          pointRadius: 2,
          pointHoverRadius: 5,
          pointBackgroundColor: '#F59E0B',
          pointBorderColor: chartSurfaceColor,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 750, easing: 'easeOutQuart' },
        plugins: { legend: { display: false }, tooltip: baseTooltipStyle },
        scales: { x: { display: false }, y: { display: false, beginAtZero: true, suggestedMax: 4 } },
      },
    });
    return;
  }

  // 5. VARIANT: DONUT STAT
  if (props.variant === 'donut-stat') {
    const rawPct = parseFloat(props.centerText ?? '0');
    const pct = Number.isFinite(rawPct) ? Math.min(100, Math.max(0, rawPct)) : 0;

    chartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Placed', 'Remaining'],
        datasets: [{
          data: [pct, Math.max(0, 100 - pct)],
          backgroundColor: ['#06B6D4', chartTrackColor],
          borderColor: chartSurfaceColor,
          borderWidth: 3,
          cutout: '76%',
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 750, easing: 'easeOutQuart' },
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
      },
    });
    return;
  }

  // 6. VARIANT: AREA WAVE
  if (props.variant === 'area-wave') {
    const waveTabs = resolvedWaveTabs.value;
    const tabKey = activeWaveTab.value;
    const currentWave = Array.isArray(waveTabs[tabKey]) ? waveTabs[tabKey] : [];
    const waveLabels = resolvedLabels.value.length
      ? resolvedLabels.value
      : currentWave.map((_, i) => String(i + 1));

    const waveColor = tabKey === 'leaves' ? '#F59E0B' : (tabKey === 'attendance' ? '#10B981' : '#06B6D4');
    const gradient = ctx.createLinearGradient(0, 0, 0, 150);
    gradient.addColorStop(0, hexToRgba(waveColor, 0.35));
    gradient.addColorStop(1, hexToRgba(waveColor, 0.0));

    chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: waveLabels,
        datasets: [{
          data: currentWave,
          borderColor: waveColor,
          backgroundColor: gradient,
          fill: true,
          tension: 0.4,
          borderWidth: 2.5,
          pointRadius: 3,
          pointHoverRadius: 6,
          pointBackgroundColor: waveColor,
          pointBorderColor: chartSurfaceColor,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 600, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: {
            ...baseTooltipStyle,
            bodyColor: waveColor,
          },
        },
        scales: {
          x: { display: false },
          y: { display: false, beginAtZero: true, suggestedMax: 4 },
        },
      },
    });
    return;
  }

  // 7. MINI BAR
  if (props.variant === 'mini-bar') {
    const barData = resolvedData.value;
    const barLabels = resolvedLabels.value;

    chartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: barLabels,
        datasets: [{
          data: barData,
          backgroundColor: '#06B6D4',
          hoverBackgroundColor: '#10B981',
          borderRadius: 4,
          borderSkipped: false,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 700, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: baseTooltipStyle,
        },
        scales: { x: { display: false }, y: { display: false, beginAtZero: true, suggestedMax: 4 } },
      },
    });
    return;
  }

  // 8. MINI ZIGZAG
  if (props.variant === 'mini-zigzag') {
    const lineData = resolvedData.value;
    const lineLabels = resolvedLabels.value.length ? resolvedLabels.value : lineData.map((_, i) => String(i + 1));
    const gradient = ctx.createLinearGradient(0, 0, 0, 100);
    gradient.addColorStop(0, 'rgba(0, 240, 255, 0.35)');
    gradient.addColorStop(1, 'rgba(0, 240, 255, 0.0)');

    chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: lineLabels,
        datasets: [{
          data: lineData,
          borderColor: '#06B6D4',
          backgroundColor: gradient,
          fill: true,
          tension: 0,
          borderWidth: 2.2,
          pointRadius: 2.5,
          pointHoverRadius: 6,
          pointBackgroundColor: '#06B6D4',
          pointBorderColor: chartSurfaceColor,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 700, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: baseTooltipStyle,
        },
        scales: { x: { display: false }, y: { display: false, beginAtZero: true, suggestedMax: 4 } },
      },
    });
    return;
  }

  // 9. DONUT CENTER
  if (props.variant === 'donut-center') {
    const rawData = resolvedData.value;
    const rawLabels = resolvedLabels.value.length ? resolvedLabels.value : ['Allocated Beds', 'Available Beds', 'Pending Placement'];
    const rawColors = resolvedColors.value.length ? resolvedColors.value : ['#06B6D4', chartTrackColor, '#8B5CF6'];
    const totalSum = rawData.reduce((sum, val) => sum + (Number(val) || 0), 0);
    const isEmpty = !rawData.length || totalSum <= 0;

    chartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: isEmpty ? ['No Data'] : rawLabels,
        datasets: [{
          data: isEmpty ? [1] : rawData,
          backgroundColor: isEmpty ? [chartTrackColor] : rawColors,
          borderColor: chartSurfaceColor,
          borderWidth: 3,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '74%',
        animation: { duration: 750, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: isEmpty ? { enabled: false } : baseTooltipStyle,
        },
      },
    });
    return;
  }

  // 10. SEMI GAUGE
  if (props.variant === 'semi-gauge') {
    const rawData = resolvedData.value;
    const rawLabels = resolvedLabels.value.length ? resolvedLabels.value : ['Male Students', 'Female Students'];
    const rawColors = resolvedColors.value.length ? resolvedColors.value : ['#06B6D4', '#F43F5E'];
    const totalSum = rawData.reduce((sum, val) => sum + (Number(val) || 0), 0);
    const isEmpty = !rawData.length || totalSum <= 0;

    chartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: isEmpty ? ['No Data'] : rawLabels,
        datasets: [{
          data: isEmpty ? [1] : rawData,
          backgroundColor: isEmpty ? [chartTrackColor] : rawColors,
          borderColor: chartSurfaceColor,
          borderWidth: 3,
          circumference: 180,
          rotation: -90,
          cutout: '72%',
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 750, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: isEmpty ? { enabled: false } : baseTooltipStyle,
        },
      },
    });
    return;
  }

  // 12. VARIANT: DONUT WITH LEGEND
  if (props.variant === 'donut-legend') {
    const rawData = resolvedData.value;
    const rawLabels = resolvedLabels.value.length ? resolvedLabels.value : ['Allocated', 'Available', 'On Leave', 'Other'];
    const rawColors = resolvedColors.value.length ? resolvedColors.value : ['#10B981', '#06B6D4', '#F59E0B', '#8B5CF6'];
    const totalSum = rawData.reduce((sum, val) => sum + (Number(val) || 0), 0);
    const isEmpty = !rawData.length || totalSum <= 0;

    chartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: isEmpty ? ['No Data'] : rawLabels,
        datasets: [{
          data: isEmpty ? [1] : rawData,
          backgroundColor: isEmpty ? [chartTrackColor] : rawColors,
          borderColor: chartSurfaceColor,
          borderWidth: 3,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        animation: { duration: 750, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: isEmpty
            ? { enabled: false }
            : {
                ...baseTooltipStyle,
                bodyColor: isLight ? '#047857' : '#10B981',
              },
        },
      },
    });
    return;
  }

  // 13. VARIANT: DAILY MULTI-LINE
  if (props.variant === 'daily-multi-line') {
    const primaryData = resolvedData.value;
    const secondaryData = resolvedSecondaryData.value;
    const maxLen = Math.max(primaryData.length, secondaryData.length);
    const chartLabels = resolvedLabels.value.length
      ? resolvedLabels.value
      : Array.from({ length: maxLen }, (_, i) => String(i + 1));

    const primaryLabel = resolvedLegendItems.value[0]?.label || 'Check-ins';
    const secondaryLabel = resolvedLegendItems.value[1]?.label || 'Gate Passes';

    const blueGradient = ctx.createLinearGradient(0, 0, 0, 280);
    blueGradient.addColorStop(0, 'rgba(0, 240, 255, 0.28)');
    blueGradient.addColorStop(1, 'rgba(0, 240, 255, 0.0)');

    chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: chartLabels,
        datasets: [
          {
            label: primaryLabel,
            data: primaryData,
            borderColor: '#F59E0B',
            backgroundColor: 'transparent',
            tension: 0.35,
            borderWidth: 2.2,
            pointRadius: 3.5,
            pointHoverRadius: 6,
            pointBackgroundColor: '#F59E0B',
            pointBorderColor: chartSurfaceColor,
            pointBorderWidth: 2,
          },
          {
            label: secondaryLabel,
            data: secondaryData,
            borderColor: '#06B6D4',
            backgroundColor: blueGradient,
            fill: true,
            tension: 0.4,
            borderWidth: 2.2,
            pointRadius: 0,
            pointHoverRadius: 5,
            pointBackgroundColor: '#06B6D4',
            pointBorderColor: chartSurfaceColor,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 750, easing: 'easeOutQuart' },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            ...baseTooltipStyle,
            padding: 10,
          },
        },
        scales: {
          x: {
            grid: { color: chartGridColor, drawBorder: false },
            ticks: { color: chartTickColor, font: { family: 'Figtree', size: 10 } },
          },
          y: {
            beginAtZero: true,
            suggestedMax: 4,
            grid: { color: chartGridColor, drawBorder: false },
            ticks: { color: chartTickColor, precision: 0 },
          },
        },
      },
    });
    return;
  }

  // 14. VARIANT: STAT WITH MINI BAR
  if (props.variant === 'stat-mini-bar') {
    const barData = resolvedData.value;
    const barLabels = resolvedLabels.value.length ? resolvedLabels.value : barData.map((_, i) => String(i + 1));
    const barColor = props.colors && props.colors.length
      ? (Array.isArray(props.colors) ? props.colors[0] : props.colors)
      : '#06B6D4';

    chartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: barLabels,
        datasets: [{
          data: barData,
          backgroundColor: barColor,
          hoverBackgroundColor: '#10B981',
          borderRadius: 3,
          borderSkipped: false,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 700, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: baseTooltipStyle,
        },
        scales: { x: { display: false }, y: { display: false, beginAtZero: true, suggestedMax: 4 } },
      },
    });
    return;
  }

  // 11. FALLBACK DEFAULT
  chartInstance = new Chart(ctx, {
    type: props.type || 'bar',
    data: {
      labels: resolvedLabels.value,
      datasets: resolvedDatasets.value.length ? resolvedDatasets.value : [{
        label: props.label,
        data: resolvedData.value,
        backgroundColor: '#06B6D4',
      }],
    },
    options: { responsive: true, maintainAspectRatio: false },
  });
};

onMounted(() => {
  window.addEventListener('theme-changed', renderChart);
  animateNumber();
  renderChart();
});

watch(
  [
    () => props.labels,
    () => props.data,
    () => props.datasets,
    () => props.colors,
    () => props.secondaryData,
    () => props.centerText,
    () => props.waveTabsData,
    () => props.legendItems,
    activeWaveTab,
  ],
  () => {
    renderChart();
  },
  { deep: true }
);

onUnmounted(() => {
  window.removeEventListener('theme-changed', renderChart);
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }
});

watch(() => props.value, () => {
  animateNumber();
});
</script>
