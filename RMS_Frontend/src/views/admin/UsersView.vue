<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">User Management</h1>
        <p class="text-xs text-[#7a6e5a] mt-0.5">
          Role-Based Access Control (RBAC), account status, and subsidiary data isolation
        </p>
      </div>

      <button
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] shadow-sm transition-all cursor-pointer"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        Add Internal User
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-[#f0e9dc] flex flex-wrap items-center gap-4">
      <div class="flex-1 min-w-[200px]">
        <input
          v-model="filters.search"
          @input="loadUsers"
          type="text"
          placeholder="Search by name or email"
          class="w-full px-3.5 py-1.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] bg-neutral-50"
        />
      </div>

      <select
        v-model="filters.role"
        @change="loadUsers"
        class="pl-3.5 pr-8 py-1.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] bg-neutral-50 cursor-pointer"
      >
        <option value="">All Roles</option>
        <option value="Admin">Admin</option>
        <option value="HR Manager">HR Manager</option>
        <option value="Recruiter">Recruiter</option>
        <option value="Hiring Manager">Hiring Manager</option>
        <option value="Applicant">Applicant</option>
      </select>

      <select
        v-model="filters.status"
        @change="loadUsers"
        class="pl-3.5 pr-8 py-1.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] bg-neutral-50 cursor-pointer"
      >
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="suspended">Suspended</option>
      </select>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] shadow-xs overflow-hidden">
      <div v-if="loading" class="p-12 text-center text-sm text-[#7a6e5a]">
        Loading users...
      </div>

      <div v-else-if="users.length === 0" class="p-12 text-center text-sm text-neutral-500">
        No users match the selected criteria.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-[#faf8f5] text-xs font-bold tracking-wider text-[#7a6e5a] border-b border-[#f0e9dc]">
            <tr>
              <th class="py-3.5 px-5">User</th>
              <th class="py-3.5 px-5">Role</th>
              <th class="py-3.5 px-5">Assigned Company</th>
              <th class="py-3.5 px-5">Status</th>
              <th class="py-3.5 px-5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100">
            <tr v-for="user in users" :key="user.id" class="hover:bg-neutral-50/50">
              <td class="py-4 px-5">
                <div class="font-bold text-neutral-900">{{ user.name }}</div>
                <div class="text-xs text-[#7a6e5a]">{{ user.email }}</div>
              </td>
              <td class="py-4 px-5">
                <span class="text-sm font-semibold" :class="getRoleBadgeClass(user.role)">
                  {{ user.role }}
                </span>
              </td>
              <td class="py-4 px-5">
                <span v-if="user.subsidiary" class="text-xs font-medium text-neutral-700">
                  {{ user.subsidiary.name }}
                </span>
                <span v-else class="text-xs font-medium text-[#db802d]">
                  Tora Holding Company
                </span>
              </td>
              <td class="py-4 px-5">
                <span class="text-sm font-semibold capitalize" :class="getStatusBadgeClass(user.status)">
                  {{ user.status }}
                </span>
                <span v-if="user.is_locked" class="ml-1 text-[10px] text-red-600 font-bold">
                  (Locked)
                </span>
              </td>
              <td class="py-4 px-5 text-right space-x-1.5">
                <!-- Edit Icon Button -->
                <button
                  @click="openEditModal(user)"
                  class="p-2 text-neutral-500 hover:text-[#db802d] hover:bg-amber-50 rounded-xl transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Edit User"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <!-- Suspend Icon Button -->
                <button
                  v-if="user.status !== 'suspended'"
                  @click="updateUserStatus(user, 'suspended')"
                  class="p-2 text-neutral-500 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Suspend User"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                  </svg>
                </button>
                <!-- Activate Icon Button -->
                <button
                  v-else
                  @click="updateUserStatus(user, 'active')"
                  class="p-2 text-neutral-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Activate User"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">
            {{ isEditing ? 'Edit User' : 'Create Internal User' }}
          </h3>
          <button @click="showModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="handleSubmit" novalidate class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Full Name *</label>
            <input
              v-model="userForm.name"
              type="text"
              placeholder="Enter full name"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d]"
              :class="formErrors.name ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="formErrors.name" class="text-xs text-red-600 mt-1 font-medium">{{ formErrors.name }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Email Address *</label>
            <input
              v-model="userForm.email"
              type="email"
              :disabled="isEditing"
              placeholder="Enter email address"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] disabled:bg-neutral-100"
              :class="formErrors.email ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="formErrors.email" class="text-xs text-red-600 mt-1 font-medium">{{ formErrors.email }}</p>
          </div>

          <div v-if="!isEditing">
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Initial Password *</label>
            <input
              v-model="userForm.password"
              type="password"
              placeholder="Enter password"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d]"
              :class="formErrors.password ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="formErrors.password" class="text-xs text-red-600 mt-1 font-medium">{{ formErrors.password }}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Role *</label>
              <select
                v-model="userForm.role"
                class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] cursor-pointer"
              >
                <option value="Admin">Admin</option>
                <option value="HR Manager">HR Manager</option>
                <option value="Recruiter">Recruiter</option>
                <option value="Hiring Manager">Hiring Manager</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Status</label>
              <select
                v-model="userForm.status"
                class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] cursor-pointer"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">
              Company Scope (Subsidiary)
            </label>
            <select
              v-model="userForm.subsidiary_id"
              class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] cursor-pointer"
            >
              <option :value="null">Tora Holding Company</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div class="pt-4 flex items-center justify-end gap-3 border-t border-neutral-100">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 rounded-xl text-sm font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] disabled:opacity-50 cursor-pointer"
            >
              {{ saving ? 'Saving...' : (isEditing ? 'Update User' : 'Create User') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../../api/client'
import { useNotificationStore } from '../../stores/notification'
import { useOrganizationStore } from '../../stores/organization'

const orgStore = useOrganizationStore()
const notificationStore = useNotificationStore()

const users = ref([])
const companies = ref([])
const loading = ref(false)
const saving = ref(false)

const filters = reactive({
  search: '',
  role: '',
  status: '',
})

const showModal = ref(false)
const isEditing = ref(false)
const currentUserId = ref(null)

const userForm = reactive({
  name: '',
  email: '',
  password: '',
  role: 'Recruiter',
  status: 'active',
  subsidiary_id: null,
})

const loadUsers = async () => {
  loading.value = true
  try {
    const response = await api.get('/users', { params: filters })
    users.value = response.data.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const formErrors = reactive({
  name: '',
  email: '',
  password: '',
})

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'Admin': return 'text-purple-700 font-bold'
    case 'HR Manager': return 'text-amber-700 font-bold'
    case 'Recruiter': return 'text-blue-700 font-bold'
    case 'Hiring Manager': return 'text-emerald-700 font-bold'
    default: return 'text-neutral-700 font-medium'
  }
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'active': return 'text-emerald-700 font-semibold'
    case 'inactive': return 'text-neutral-500 font-semibold'
    case 'suspended': return 'text-red-600 font-semibold'
    default: return 'text-neutral-600 font-medium'
  }
}

const openCreateModal = () => {
  isEditing.value = false
  currentUserId.value = null
  userForm.name = ''
  userForm.email = ''
  userForm.password = ''
  userForm.role = 'Recruiter'
  userForm.status = 'active'
  userForm.subsidiary_id = null
  formErrors.name = ''
  formErrors.email = ''
  formErrors.password = ''
  showModal.value = true
}

const openEditModal = (user) => {
  isEditing.value = true
  currentUserId.value = user.id
  userForm.name = user.name
  userForm.email = user.email
  userForm.role = user.role
  userForm.status = user.status
  userForm.subsidiary_id = user.subsidiary_id
  formErrors.name = ''
  formErrors.email = ''
  formErrors.password = ''
  showModal.value = true
}

const validateUserForm = () => {
  formErrors.name = ''
  formErrors.email = ''
  formErrors.password = ''
  let valid = true

  if (!userForm.name || !userForm.name.trim()) {
    formErrors.name = 'This field is required.'
    valid = false
  }

  if (!userForm.email || !userForm.email.trim()) {
    formErrors.email = 'This field is required.'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(userForm.email.trim())) {
    formErrors.email = 'Please enter a valid email address.'
    valid = false
  }

  if (!isEditing.value) {
    if (!userForm.password) {
      formErrors.password = 'This field is required.'
      valid = false
    } else if (userForm.password.length < 8) {
      formErrors.password = 'Please enter a valid password (minimum 8 characters).'
      valid = false
    }
  }

  return valid
}

const handleSubmit = async () => {
  if (!validateUserForm()) return

  saving.value = true
  try {
    if (isEditing.value) {
      await api.put(`/users/${currentUserId.value}`, userForm)
      notificationStore.success('User updated successfully.')
    } else {
      await api.post('/users', userForm)
      notificationStore.success('User created successfully.')
    }
    showModal.value = false
    await loadUsers()
  } catch (err) {
    if (err.response?.data?.errors) {
      const respErrors = err.response.data.errors
      if (respErrors.name) formErrors.name = respErrors.name[0]
      if (respErrors.email) formErrors.email = respErrors.email[0]
      if (respErrors.password) formErrors.password = respErrors.password[0]
    } else {
      notificationStore.error(err.response?.data?.message || 'Error saving user.')
    }
  } finally {
    saving.value = false
  }
}

const updateUserStatus = async (user, newStatus) => {
  try {
    await api.patch(`/users/${user.id}/status`, { status: newStatus })
    notificationStore.info(`User status updated to ${newStatus}.`)
    await loadUsers()
  } catch (err) {
    notificationStore.error('Failed to update status.')
  }
}

onMounted(async () => {
  companies.value = await orgStore.fetchCompanies('active')
  await loadUsers()
})
</script>
