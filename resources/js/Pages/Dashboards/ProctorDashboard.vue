<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <DashboardLayout title="Proctor Dashboard">
    <template #sidebar-links>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">Register Room (Dorm)</Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">Allocate Student</Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">Generate Report</Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">Give Comment</Link>
    </template>

    <div class="bg-white p-6 shadow rounded-lg">
      <p class="text-gray-700">Manage assigned rooms, place students into dorms, and post comments.</p>
    </div>
  </DashboardLayout>
</template>
