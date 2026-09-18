<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Departments</h1>
        <p class="text-xs text-[#7a6e5a] mt-0.5">
          Manage organizational departments associated with each company (FR-ORG-002, FR-ORG-003)
        </p>
      </div>

      <button
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] shadow-sm transition-all cursor-pointer"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Department
      </button>
    </div>

    <!-- Filter by Company -->
    <div class="bg-white p-4 rounded-2xl border border-[#f0e9dc] flex flex-wrap items-center gap-4">
      <label class="text-xs font-bold tracking-wider text-[#7a6e5a]">Filter By Company:</label>
      <select
        v-model="selectedCompanyFilter"
        @change="loadDepartments"
        class="pl-3.5 pr-8 py-1.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] bg-neutral-50 cursor-pointer"
      >
        <option value="">All Companies</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>

    <!-- Department Table -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] shadow-xs overflow-hidden">
      <div v-if="loading" class="p-12 text-center text-sm text-[#7a6e5a]">
        Loading departments...
      </div>

      <div v-else-if="departments.length === 0" class="p-12 text-center text-sm text-neutral-500">
        No departments found for the selected company.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-[#faf8f5] text-xs font-bold tracking-wider text-[#7a6e5a] border-b border-[#f0e9dc]">
            <tr>
              <th class="py-3.5 px-5">Department Name</th>
              <th class="py-3.5 px-5">Company</th>
              <th class="py-3.5 px-5">Status</th>
              <th class="py-3.5 px-5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100">
            <tr v-for="dept in departments" :key="dept.id" class="hover:bg-neutral-50/50">
              <td class="py-4 px-5 font-bold text-neutral-800">{{ dept.name }}</td>
              <td class="py-4 px-5 text-neutral-600">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-[#db802d] text-xs font-medium border border-amber-200">
                  {{ dept.subsidiary?.name || 'Holding / Unassigned' }}
                </span>
              </td>
              <td class="py-4 px-5">
                <span
                  class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                  :class="dept.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-neutral-100 text-neutral-600'"
                >
                  {{ dept.status }}
                </span>
              </td>
              <td class="py-4 px-5 text-right space-x-2">
                <button
                  @click="openEditModal(dept)"
                  class="text-xs text-[#7a6e5a] hover:text-neutral-900 font-medium px-2 py-1 rounded-md hover:bg-neutral-100"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">
            {{ isEditing ? 'Edit Department' : 'Add New Department' }}
          </h3>
          <button @click="showModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="handleSubmit" novalidate class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Company *</label>
            <select
              v-model="form.subsidiary_id"
              class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] cursor-pointer"
              :class="errors.subsidiary_id ? 'border-red-400 bg-red-50/20' : ''"
            >
              <option value="" disabled>Select Company</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <p v-if="errors.subsidiary_id" class="text-xs text-red-600 mt-1 font-medium">{{ errors.subsidiary_id }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Department Name *</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Enter department name"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d]"
              :class="errors.name ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="errors.name" class="text-xs text-red-600 mt-1 font-medium">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Status</label>
            <select
              v-model="form.status"
              class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] focus:border-[#db802d] cursor-pointer"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
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
              {{ saving ? 'Saving...' : (isEditing ? 'Update Department' : 'Create Department') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useNotificationStore } from '../../stores/notification'
import { useOrganizationStore } from '../../stores/organization'

const orgStore = useOrganizationStore()
const notificationStore = useNotificationStore()

const companies = ref([])
const departments = ref([])
const selectedCompanyFilter = ref('')
const loading = ref(false)
const saving = ref(false)

const showModal = ref(false)
const isEditing = ref(false)
const currentId = ref(null)

const form = reactive({
  subsidiary_id: '',
  name: '',
  status: 'active',
})

const errors = reactive({
  subsidiary_id: '',
  name: '',
})

const validateDeptForm = () => {
  errors.subsidiary_id = ''
  errors.name = ''
  let valid = true

  if (!form.subsidiary_id) {
    errors.subsidiary_id = 'This field is required.'
    valid = false
  }

  if (!form.name.trim()) {
    errors.name = 'This field is required.'
    valid = false
  }

  return valid
}

const loadDepartments = async () => {
  loading.value = true
  try {
    const data = await orgStore.fetchDepartments(selectedCompanyFilter.value || null)
    departments.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  currentId.value = null
  form.subsidiary_id = selectedCompanyFilter.value || (companies.value[0]?.id || '')
  form.name = ''
  form.status = 'active'
  errors.subsidiary_id = ''
  errors.name = ''
  showModal.value = true
}

const openEditModal = (dept) => {
  isEditing.value = true
  currentId.value = dept.id
  form.subsidiary_id = dept.subsidiary_id
  form.name = dept.name
  form.status = dept.status
  errors.subsidiary_id = ''
  errors.name = ''
  showModal.value = true
}

const handleSubmit = async () => {
  if (!validateDeptForm()) return

  saving.value = true
  try {
    if (isEditing.value) {
      await orgStore.updateDepartment(currentId.value, form)
      notificationStore.success('Department updated successfully.')
    } else {
      await orgStore.createDepartment(form)
      notificationStore.success('Department created successfully.')
    }
    showModal.value = false
    await loadDepartments()
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Error saving department.')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  companies.value = await orgStore.fetchCompanies('active')
  await loadDepartments()
})
</script>
