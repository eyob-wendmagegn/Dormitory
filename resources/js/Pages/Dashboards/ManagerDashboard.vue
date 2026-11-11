<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <DashboardLayout title="Proctor Manager Dashboard">
    <template #sidebar-links>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">View Students</Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">Register Block & Allocate Proctor</Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">Generate Reports</Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">View Comments</Link>
    </template>

    <div class="bg-white p-6 shadow rounded-lg">
      <p class="text-gray-700">Manage blocks, assign proctors, and review dorm analytical reports here.</p>
    </div>
  </DashboardLayout>
</template>