<template>
  <div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Companies</h1>
        <p class="text-xs text-[#7a6e5a] mt-0.5">
          Holding entities and subsidiaries operating under Tora Holding Company (FR-ORG-001, BR-003)
        </p>
      </div>

      <button
        v-if="authStore.isAdmin"
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] shadow-sm transition-all cursor-pointer"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Company
      </button>
    </div>

    <!-- Feedback Message -->
    <div v-if="feedbackMessage" class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800 flex items-center justify-between">
      <span>{{ feedbackMessage }}</span>
      <button @click="feedbackMessage = ''" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
    </div>

    <!-- Companies List Card -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] shadow-xs overflow-hidden">
      <div class="p-4 border-b border-[#f0e9dc] bg-[#faf8f5]/50 flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-[#7a6e5a]">Registered Companies ({{ companies.length }})</span>
        <button @click="loadCompanies" class="text-xs text-[#db802d] hover:underline font-semibold flex items-center gap-1 cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh
        </button>
      </div>

      <div v-if="loading" class="p-12 text-center text-sm text-[#7a6e5a]">
        Loading companies...
      </div>

      <div v-else-if="companies.length === 0" class="p-12 text-center text-sm text-neutral-500">
        No companies found. Click "Add Company" to create the first one.
      </div>

      <div v-else class="divide-y divide-neutral-100">
        <div
          v-for="company in companies"
          :key="company.id"
          class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-neutral-50/50 transition-colors"
        >
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-[#db802d] font-bold text-lg shrink-0">
              {{ company.code ? company.code.substring(0, 3) : 'TOR' }}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-bold text-neutral-900 text-base">{{ company.name }}</h3>
                <span
                  class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                  :class="company.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-neutral-100 text-neutral-600'"
                >
                  {{ company.status }}
                </span>
              </div>
              <div class="text-xs text-[#7a6e5a] mt-1 flex flex-wrap items-center gap-3">
                <span>Code: <strong class="text-neutral-700">{{ company.code }}</strong></span>
                <span>•</span>
                <span>Departments: <strong class="text-neutral-700">{{ company.departments_count || 0 }}</strong></span>
                <span>•</span>
                <span>Created: {{ new Date(company.created_at).toLocaleDateString() }}</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div v-if="authStore.isAdmin" class="flex items-center gap-2">
            <button
              @click="openEditModal(company)"
              class="px-3 py-1.5 rounded-xl border border-neutral-200 text-xs font-medium text-neutral-700 hover:bg-neutral-100 transition-colors cursor-pointer"
            >
              Edit
            </button>
            <button
              @click="handleToggleStatus(company)"
              class="px-3 py-1.5 rounded-xl border text-xs font-medium transition-colors cursor-pointer"
              :class="company.status === 'active' ? 'border-amber-200 text-amber-800 hover:bg-amber-50' : 'border-emerald-200 text-emerald-800 hover:bg-emerald-50'"
            >
              {{ company.status === 'active' ? 'Deactivate' : 'Activate' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form (Create / Edit) -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">
            {{ isEditing ? 'Edit Company' : 'Add New Company' }}
          </h3>
          <button @click="showModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="handleSubmit" class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Company Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="e.g. Tora Manufacturing Ltd."
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#ebb630]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Company Code / Acronym *</label>
            <input
              v-model="form.code"
              type="text"
              required
              placeholder="e.g. TMF"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-[#ebb630]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Status</label>
            <select
              v-model="form.status"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#ebb630]"
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
              {{ saving ? 'Saving...' : (isEditing ? 'Update Company' : 'Create Company') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useOrganizationStore } from '../../stores/organization'

const authStore = useAuthStore()
const orgStore = useOrganizationStore()

const companies = ref([])
const loading = ref(false)
const saving = ref(false)
const feedbackMessage = ref('')

const showModal = ref(false)
const isEditing = ref(false)
const currentId = ref(null)

const form = reactive({
  name: '',
  code: '',
  status: 'active',
})

const loadCompanies = async () => {
  loading.value = true
  try {
    const data = await orgStore.fetchCompanies()
    companies.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  currentId.value = null
  form.name = ''
  form.code = ''
  form.status = 'active'
  showModal.value = true
}

const openEditModal = (company) => {
  isEditing.value = true
  currentId.value = company.id
  form.name = company.name
  form.code = company.code
  form.status = company.status
  showModal.value = true
}

const handleSubmit = async () => {
  saving.value = true
  try {
    if (isEditing.value) {
      await orgStore.updateCompany(currentId.value, form)
      feedbackMessage.value = 'Company updated successfully.'
    } else {
      await orgStore.createCompany(form)
      feedbackMessage.value = 'Company created successfully.'
    }
    showModal.value = false
    await loadCompanies()
  } catch (err) {
    alert(err.response?.data?.message || 'Error saving company.')
  } finally {
    saving.value = false
  }
}

const handleToggleStatus = async (company) => {
  const newStatus = company.status === 'active' ? 'inactive' : 'active'
  try {
    await orgStore.updateCompany(company.id, {
      name: company.name,
      code: company.code,
      status: newStatus,
    })
    feedbackMessage.value = `Company status changed to ${newStatus}.`
    await loadCompanies()
  } catch (err) {
    alert('Failed to update status.')
  }
}

onMounted(() => {
  loadCompanies()
})
</script>
