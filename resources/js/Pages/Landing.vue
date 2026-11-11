<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-between">
    <!-- Top Navigation Bar -->
    <nav class="flex justify-between items-center px-8 py-5 bg-white shadow-sm">
      <div class="text-2xl font-extrabold text-indigo-600">DormManager</div>
      <div class="space-x-4">
        <Link href="/activate" class="text-indigo-600 font-semibold hover:underline">
          Activate Account
        </Link>
        <Link href="/login" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition">
          Login
        </Link>
      </div>
    </nav>

    <!-- Hero Content -->
    <div class="max-w-4xl mx-auto text-center px-6 py-20">
      <h1 class="text-5xl font-extrabold text-gray-900 leading-tight mb-6">
        Streamlined Student Dormitory & Housing Management
      </h1>
      <p class="text-lg text-gray-600 mb-8">
        A centralized digital hub for managing allocations, blocks, proctoring tasks, feedback, and student housing services smoothly.
      </p>
      <div class="flex justify-center space-x-4">
        <Link href="/activate" class="bg-indigo-600 text-white px-6 py-3 rounded-md font-semibold hover:bg-indigo-700">
          First Time? Activate Your Account
        </Link>
      </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-6 text-gray-500 text-sm border-t">
      &copy; {{ new Date().getFullYear() }} Dormitory Management System.
    </footer>
  </div>
</template>