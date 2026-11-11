<template>
  <div class="rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] overflow-hidden shadow-xl shadow-black/20">
    <!-- Header with title and search box -->
    <div class="p-5 border-b border-white/[0.06] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h3 class="text-sm font-bold text-white tracking-tight">{{ title }}</h3>
        <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">{{ subtitle }}</p>
      </div>

      <div class="w-full sm:w-64">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 transition-colors"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-300">
        <thead class="bg-[#172134]/70 text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-white/[0.06]">
          <tr>
            <th v-for="col in columns" :key="col.key" class="px-5 py-3.5 font-bold">
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/[0.04]">
          <tr v-for="(row, idx) in paginatedRows" :key="idx" class="hover:bg-white/[0.02] transition-colors">
            <td v-for="col in columns" :key="col.key" class="px-5 py-3.5 text-white">
              <slot :name="col.key" :row="row" :value="row[col.key]">
                {{ row[col.key] }}
              </slot>
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-if="filteredRows.length === 0">
            <td :colspan="columns.length" class="px-5 py-10 text-center text-slate-500">
              {{ emptyText }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <div v-if="filteredRows.length > pageSize" class="p-4 border-t border-white/[0.06] flex items-center justify-between text-xs text-slate-400">
      <span>
        Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredRows.length) }} of {{ filteredRows.length }} entries
      </span>

      <div class="flex items-center gap-1.5">
        <button
          :disabled="currentPage === 1"
          @click="currentPage--"
          class="px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/10 hover:border-emerald-500/40 hover:text-white disabled:opacity-40 disabled:hover:border-white/10 text-slate-300 transition-colors"
        >
          Previous
        </button>
        <span class="px-2.5 font-semibold text-emerald-400">{{ currentPage }} / {{ totalPages }}</span>
        <button
          :disabled="currentPage >= totalPages"
          @click="currentPage++"
          class="px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/10 hover:border-emerald-500/40 hover:text-white disabled:opacity-40 disabled:hover:border-white/10 text-slate-300 transition-colors"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  columns: { type: Array, required: true },
  rows: { type: Array, default: () => [] },
  pageSize: { type: Number, default: 5 },
  searchPlaceholder: { type: String, default: 'Search records...' },
  emptyText: { type: String, default: 'No matching records found.' },
});

const searchQuery = ref('');
const currentPage = ref(1);

const filteredRows = computed(() => {
  if (!searchQuery.value.trim()) return props.rows;
  const q = searchQuery.value.toLowerCase();
  return props.rows.filter(row => {
    return Object.values(row).some(val => String(val).toLowerCase().includes(q));
  });
});

const totalPages = computed(() => Math.ceil(filteredRows.value.length / props.pageSize) || 1);

const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * props.pageSize;
  return filteredRows.value.slice(start, start + props.pageSize);
});
</script>
