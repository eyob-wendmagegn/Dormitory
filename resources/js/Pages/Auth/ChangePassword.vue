<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  userId: Number,
  email: String,
});

const form = useForm({
  user_id: props.userId,
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('activate.password.set'));
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-2 text-center text-gray-800">Set New Password</h2>
      <p class="text-sm text-gray-500 mb-6 text-center">Activating account for {{ email }}</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">New Password</label>
          <input v-model="form.password" type="password" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm p-2 border" />
          <span v-if="form.errors.password" class="text-red-500 text-xs">{{ form.errors.password }}</span>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" required class="w-full mt-1 border-gray-300 rounded-md shadow-sm p-2 border" />
        </div>

        <button :disabled="form.processing" type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700">
          Save Password & Activate
        </button>
      </form>
    </div>
  </div>
</template>