<template>
  <div
    class="p-5 rounded-2xl bg-[#172134]/60 hover:bg-[#172134]/90 border border-white/[0.05] hover:border-white/[0.12] transition-all duration-200 group relative overflow-hidden flex flex-col justify-between shadow-lg shadow-black/20"
  >
    <!-- Subtle ambient corner glow on hover -->
    <div
      class="absolute -right-4 -bottom-4 w-16 h-16 rounded-full blur-xl transition-all duration-300 pointer-events-none opacity-20 group-hover:opacity-50"
      :class="glowColorClass"
    ></div>

    <div class="flex items-start justify-between gap-3">
      <div class="space-y-1 min-w-0">
        <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 truncate block">
          {{ title }}
        </span>
        <div
          class="text-2xl sm:text-3xl font-semibold tracking-tight font-mono"
          :class="accentColorClass"
        >
          {{ displayPrefix }}{{ formattedDisplayValue }}{{ displaySuffix }}
        </div>
      </div>

      <!-- Icon Box -->
      <div
        class="w-10 h-10 rounded-xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center transition-all shrink-0"
        :class="iconBoxClass"
      >
        <slot name="icon">
          <svg v-if="icon === 'users'" class="w-4 h-4 sm:w-5 sm:h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <svg v-else-if="icon === 'check-circle'" class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else-if="icon === 'clock'" class="w-4 h-4 sm:w-5 sm:h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else-if="icon === 'building'" class="w-4 h-4 sm:w-5 sm:h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <svg v-else-if="icon === 'door'" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v.01M12 4.5v15m0-15l7 2.5v12.5l-7 2.5m0-17.5L5 7v12.5l7 2.5" />
          </svg>
          <svg v-else-if="icon === 'shield'" class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <svg v-else-if="icon === 'briefcase'" class="w-4 h-4 sm:w-5 sm:h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <svg v-else class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </slot>
      </div>
    </div>

    <!-- Subtitle / Footer -->
    <div
      v-if="subtitle || badge"
      class="mt-3 pt-2.5 border-t border-white/[0.06] flex items-center justify-between text-xs text-slate-400"
    >
      <span class="truncate">{{ subtitle }}</span>
      <span v-if="badge" class="font-medium text-emerald-400 ml-1 shrink-0">{{ badge }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], required: true },
  subtitle: { type: String, default: '' },
  badge: { type: String, default: '' },
  color: { type: String, default: 'white' },
  icon: { type: String, default: '' },
});

const displayNumeric = ref(0);
const displayPrefix = ref('');
const displaySuffix = ref('');
const isNonNumeric = ref(false);
const rawTextValue = ref('');

const formattedDisplayValue = computed(() => {
  if (isNonNumeric.value) return rawTextValue.value;
  return displayNumeric.value.toLocaleString();
});

function parseValue(val) {
  if (val === undefined || val === null) {
    return { isNum: false, num: 0, pre: '', suf: '', text: '' };
  }
  if (typeof val === 'number') {
    return { isNum: true, num: val, pre: '', suf: '', text: String(val) };
  }
  const str = String(val).trim();
  const cleanStr = str.replace(/,/g, '');
  const match = cleanStr.match(/^([^\d\-+]*)([-+]?[0-9]+(?:\.[0-9]+)?)(.*)$/);
  if (match) {
    return {
      isNum: true,
      num: parseFloat(match[2]),
      pre: match[1],
      suf: match[3],
      text: str,
    };
  }
  return { isNum: false, num: 0, pre: '', suf: '', text: str };
}

function startFastCountUp() {
  const parsed = parseValue(props.value);
  if (!parsed.isNum) {
    isNonNumeric.value = true;
    rawTextValue.value = parsed.text;
    return;
  }

  isNonNumeric.value = false;
  displayPrefix.value = parsed.pre;
  displaySuffix.value = parsed.suf;

  const target = parsed.num;
  const start = 0;
  const duration = 650;
  const startTime = performance.now();

  function tick(currentTime) {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const ease = 1 - Math.pow(1 - progress, 4);
    const current = Math.round(start + (target - start) * ease);

    displayNumeric.value = current;

    if (progress < 1) {
      requestAnimationFrame(tick);
    } else {
      displayNumeric.value = target;
    }
  }

  requestAnimationFrame(tick);
}

onMounted(() => {
  startFastCountUp();
});

watch(() => props.value, () => {
  startFastCountUp();
});

const accentColorClass = computed(() => {
  if (props.color === 'accent' || props.color === 'lime' || props.color === 'emerald') return 'text-emerald-400';
  if (props.color === 'secondary' || props.color === 'cyan') return 'text-cyan-400';
  if (props.color === 'purple' || props.color === 'violet') return 'text-violet-400';
  if (props.color === 'amber') return 'text-amber-400';
  return 'text-white';
});

const glowColorClass = computed(() => {
  if (props.color === 'lime' || props.color === 'emerald') return 'bg-emerald-500';
  if (props.color === 'cyan') return 'bg-cyan-500';
  if (props.color === 'purple' || props.color === 'violet') return 'bg-violet-500';
  return 'bg-emerald-500';
});

const iconBoxClass = computed(() => {
  if (props.color === 'lime' || props.color === 'emerald') return 'text-emerald-400 border-emerald-500/20 bg-emerald-500/10';
  if (props.color === 'secondary' || props.color === 'cyan') return 'text-cyan-400 border-cyan-500/20 bg-cyan-500/10';
  if (props.color === 'purple' || props.color === 'violet') return 'text-violet-400 border-violet-500/20 bg-violet-500/10';
  return 'text-slate-300 border-white/[0.08]';
});
</script>
