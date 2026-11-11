<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <DashboardLayout title="Student Dashboard">
    <template #sidebar-links>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">View Dorm Details</Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">Give Comment / Feedback</Link>
    </template>

    <div class="bg-white p-6 shadow rounded-lg">
      <p class="text-gray-700">Welcome! View your assigned dorm room, block information, and submit queries.</p>
    </div>
  </DashboardLayout>
</template>