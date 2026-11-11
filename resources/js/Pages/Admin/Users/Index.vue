<script setup>
import { ref } from 'vue';
import { useForm, usePage, Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
  users: Array,
});

const isModalOpen = ref(false);

const form = useForm({
  name: '',
  user_id_number: '',
  email: '',
  role: 'student',
});

const openModal = () => {
  form.reset();
  form.clearErrors();
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
};

const submitUser = () => {
  form.post(route('admin.users.store'), {
    onSuccess: () => {
      closeModal();
    },
  });
};

const deleteUser = (userId) => {
  if (confirm('Are you sure you want to remove this user?')) {
    form.delete(route('admin.users.destroy', userId));
  }
};

const getRoleBadge = (role) => {
  switch (role) {
    case 'admin':
      return 'bg-purple-100 text-purple-800 border-purple-200';
    case 'proctor_manager':
      return 'bg-blue-100 text-blue-800 border-blue-200';
    case 'proctor':
      return 'bg-emerald-100 text-emerald-800 border-emerald-200';
    case 'student':
      return 'bg-amber-100 text-amber-800 border-amber-200';
    default:
      return 'bg-gray-100 text-gray-800 border-gray-200';
  }
};
</script>

<template>
  <Head title="User Management" />

  <DashboardLayout title="User Registration & Management">
    <template #sidebar-links>
      <Link :href="route('admin.dashboard')" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">Dashboard</Link>
      <Link :href="route('admin.users.index')" class="block px-3 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded">Register & Manage Users</Link>
      <Link href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded">View Comments</Link>
    </template>

    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h3 class="text-lg font-semibold text-gray-800">System Users</h3>
        <p class="text-sm text-gray-500">Register new users and set their roles/privileges.</p>
      </div>
      <button
        @click="openModal"
        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm flex items-center gap-2 transition"
      >
        <span>+ Register New User</span>
      </button>
    </div>

    <!-- Success Message Banner -->
    <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
      {{ $page.props.flash.success }}
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold uppercase text-gray-500 tracking-wider">
            <th class="py-3 px-4">User ID</th>
            <th class="py-3 px-4">Full Name / Username</th>
            <th class="py-3 px-4">Email</th>
            <th class="py-3 px-4">Role / Privilege</th>
            <th class="py-3 px-4">Activation Status</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition">
            <td class="py-3 px-4 font-mono font-medium text-indigo-600">{{ user.user_id_number }}</td>
            <td class="py-3 px-4 font-medium text-gray-900">{{ user.name }}</td>
            <td class="py-3 px-4 text-gray-600">{{ user.email }}</td>
            <td class="py-3 px-4">
              <span :class="['px-2.5 py-1 text-xs font-semibold rounded-full border capitalize', getRoleBadge(user.role)]">
                {{ user.role.replace('_', ' ') }}
              </span>
            </td>
            <td class="py-3 px-4">
              <span v-if="user.is_active" class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-md border border-green-200">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Active
              </span>
              <span v-else class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span> Pending Activation
              </span>
            </td>
            <td class="py-3 px-4 text-right">
              <button
                @click="deleteUser(user.id)"
                class="text-red-500 hover:text-red-700 font-medium text-xs hover:underline"
              >
                Delete
              </button>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="6" class="py-8 text-center text-gray-400">No users found. Click "Register New User" to add one.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Registration Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 relative">
        <div class="flex justify-between items-center mb-5 pb-3 border-b">
          <h3 class="text-xl font-bold text-gray-800">Register New User</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitUser" class="space-y-4">
          <!-- User ID Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">User ID Number</label>
            <input
              v-model="form.user_id_number"
              type="text"
              placeholder="e.g. STU-1002, PR-201, PM-101"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 border"
              :class="{ 'border-red-500': form.errors.user_id_number }"
            />
            <p v-if="form.errors.user_id_number" class="text-red-500 text-xs mt-1">{{ form.errors.user_id_number }}</p>
          </div>

          <!-- Name / Username -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name / Username</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Enter full name or username"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 border"
              :class="{ 'border-red-500': form.errors.name }"
            />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <!-- Email Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="user@dormitory.edu"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 border"
              :class="{ 'border-red-500': form.errors.email }"
            />
            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <!-- Role / Privilege Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Assign Privilege / Role</label>
            <select
              v-model="form.role"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 border bg-white"
            >
              <option value="student">Student</option>
              <option value="proctor">Proctor</option>
              <option value="proctor_manager">Proctor Manager</option>
              <option value="admin">Administrator</option>
            </select>
            <p v-if="form.errors.role" class="text-red-500 text-xs mt-1">{{ form.errors.role }}</p>
          </div>

          <div class="p-3 bg-indigo-50 rounded-lg text-xs text-indigo-700 border border-indigo-100">
            <strong>Note:</strong> The initial password will automatically be set to the user's <strong>User ID Number</strong>. The user must activate their account on first visit to set a new password.
          </div>

          <!-- Buttons -->
          <div class="flex justify-end gap-3 pt-3 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm shadow-sm transition disabled:opacity-50"
            >
              {{ form.processing ? 'Registering...' : 'Save & Register User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>