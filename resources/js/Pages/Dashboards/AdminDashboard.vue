<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <DashboardLayout title="Admin Control Panel">
    <template #sidebar-links>
      <!-- Active link pointing to /admin/users -->
      <Link :href="route('admin.users.index')" class="block px-3 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded">
        Create User / Assign Privilege
      </Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">View Student Info</Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">View Comments</Link>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Clickable Register User Card -->
      <Link :href="route('admin.users.index')" class="p-6 bg-white shadow rounded-lg hover:shadow-md transition border border-transparent hover:border-indigo-200 group">
        <h3 class="font-bold text-gray-800 group-hover:text-indigo-600">Register User &rarr;</h3>
        <p class="text-sm text-gray-500 mt-2">Add username and ID number to grant access.</p>
      </Link>
      
      <div class="p-6 bg-white shadow rounded-lg">
        <h3 class="font-bold text-gray-600">Student Info</h3>
        <p class="text-sm text-gray-500 mt-2">Review detailed student directory.</p>
      </div>

      <div class="p-6 bg-white shadow rounded-lg">
        <h3 class="font-bold text-gray-600">Comments</h3>
        <p class="text-sm text-gray-500 mt-2">Check feedback across blocks.</p>
      </div>
    </div>
  </DashboardLayout>
</template>