<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  title: String,
});

const user = usePage().props.auth.user;
const isSidebarOpen = ref(true);
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-100 text-gray-800">
    <!-- Header -->
    <header class="bg-indigo-700 text-white shadow flex justify-between items-center px-6 py-4">
      <div class="flex items-center space-x-4">
        <button @click="isSidebarOpen = !isSidebarOpen" class="text-xl focus:outline-none">☰</button>
        <h1 class="text-xl font-bold">Dormitory Management System</h1>
      </div>
      <div class="flex items-center space-x-4">
        <span class="text-sm bg-indigo-800 px-3 py-1 rounded-full capitalize">{{ user.role.replace('_', ' ') }}</span>
        <span class="font-medium">{{ user.name }}</span>
        <Link href="/logout" method="post" as="button" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm transition">
          Logout
        </Link>
      </div>
    </header>

    <div class="flex flex-1">
      <!-- Sidebar -->
      <aside v-if="isSidebarOpen" class="w-64 bg-white shadow-md flex flex-col justify-between p-4">
        <nav class="space-y-2">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Navigation</p>
          <slot name="sidebar-links"></slot>
        </nav>
      </aside>

      <!-- Main Content Area -->
      <main class="flex-1 p-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ title }}</h2>
        <slot />
      </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t py-4 text-center text-sm text-gray-500">
      &copy; {{ new Date().getFullYear() }} Dormitory Management System. All rights reserved.
    </footer>
  </div>
</template>