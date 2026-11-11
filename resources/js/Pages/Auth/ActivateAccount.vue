<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  name: '',
  user_id_number: '',
});

const submit = () => {
  form.post(route('activate.verify'));
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-2 text-center text-gray-800">Activate Your Account</h2>
      <p class="text-sm text-gray-500 mb-6 text-center">Enter your registered details given by the administrator.</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Full Name / Username</label>
          <input v-model="form.name" type="text" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm p-2 border" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Email Address</label>
          <input v-model="form.email" type="email" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm p-2 border" />
          <span v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</span>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">User ID Number</label>
          <input v-model="form.user_id_number" type="text" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm p-2 border" placeholder="e.g. STU-1001" />
        </div>

        <button :disabled="form.processing" type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700">
          Verify & Continue
        </button>
      </form>
    </div>
  </div>
</template>