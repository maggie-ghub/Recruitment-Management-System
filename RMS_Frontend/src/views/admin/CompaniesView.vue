<template>
  <div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-neutral-900 tracking-tight">Companies</h1>
        <p class="text-sm text-[#7a6e5a] mt-1">
          Registered business entities and subsidiaries operating across Tora Holding Company
        </p>
      </div>

      <button
        v-if="authStore.isAdmin"
        @click="openCreateModal"
        class="inline-flex items-center px-5 py-3 rounded-2xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] shadow-sm transition-all cursor-pointer"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Company
      </button>
    </div>

    <!-- Companies List Card -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] shadow-xs overflow-hidden">
      <div class="p-5 border-b border-[#f0e9dc] bg-[#faf8f5]/50 flex items-center justify-between">
        <span class="text-xs font-bold tracking-wider text-[#7a6e5a]">Registered Companies ({{ companies.length }})</span>
        <button @click="loadCompanies" class="text-xs text-[#db802d] hover:underline font-semibold flex items-center gap-1.5 cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh List
        </button>
      </div>

      <div v-if="loading" class="p-16 text-center text-base text-[#7a6e5a]">
        Loading companies...
      </div>

      <div v-else-if="companies.length === 0" class="p-16 text-center text-base text-neutral-500">
        No companies found. Click "Add Company" to create the first one.
      </div>

      <div v-else class="divide-y divide-neutral-100">
        <div
          v-for="company in companies"
          :key="company.id"
          class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 hover:bg-neutral-50/50 transition-colors"
        >
          <div class="flex items-center gap-5">
            <!-- Company Logo with Automatic Centering & Object-Cover / Contain -->
            <div class="w-16 h-16 rounded-2xl bg-transparent border border-neutral-200/80 flex items-center justify-center shrink-0 overflow-hidden relative shadow-xs">
              <img
                v-if="company.logo_url"
                :src="getLogoUrl(company)"
                :alt="company.name"
                class="w-full h-full object-contain object-center"
              />
              <span v-else class="text-base font-extrabold text-[#db802d]">
                {{ company.code ? company.code.substring(0, 3) : 'TOR' }}
              </span>
            </div>

            <div>
              <div class="flex items-center gap-3">
                <h3 class="font-extrabold text-neutral-900 text-lg">{{ company.name }}</h3>
                <span
                  class="px-3 py-0.5 rounded-full text-xs font-semibold tracking-wider"
                  :class="company.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-neutral-100 text-neutral-600'"
                >
                  {{ company.status }}
                </span>
              </div>
              <div class="text-xs sm:text-sm text-[#7a6e5a] mt-1.5 flex flex-wrap items-center gap-3">
                <span>Code: <strong class="text-neutral-800 font-mono">{{ company.code }}</strong></span>
                <span>•</span>
                <span>Departments: <strong class="text-neutral-800">{{ company.departments_count || 0 }}</strong></span>
                <span>•</span>
                <span>Created: {{ new Date(company.created_at).toLocaleDateString() }}</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div v-if="authStore.isAdmin" class="flex items-center gap-2.5">
            <button
              @click="openEditModal(company)"
              class="px-4 py-2 rounded-xl border border-neutral-200 text-xs font-semibold text-neutral-700 hover:bg-neutral-100 transition-colors cursor-pointer"
            >
              Edit
            </button>
            <button
              @click="handleToggleStatus(company)"
              class="px-4 py-2 rounded-xl border text-xs font-semibold transition-colors cursor-pointer"
              :class="company.status === 'active' ? 'border-amber-200 text-amber-800 hover:bg-amber-50' : 'border-emerald-200 text-emerald-800 hover:bg-emerald-50'"
            >
              {{ company.status === 'active' ? 'Deactivate' : 'Activate' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form (Create / Edit with Local File Upload) -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-lg w-full p-7 sm:p-8 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-4 border-b border-neutral-100">
          <h3 class="font-bold text-xl text-neutral-900">
            {{ isEditing ? 'Edit Company' : 'Add New Company' }}
          </h3>
          <button @click="showModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-2xl cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="handleSubmit" novalidate class="mt-5 space-y-4">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Company Name *</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Enter company name"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="errors.name ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="errors.name" class="text-xs text-red-600 mt-1 font-medium">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Company Code / Acronym *</label>
            <input
              v-model="form.code"
              type="text"
              placeholder="Enter company code"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="errors.code ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="errors.code" class="text-xs text-red-600 mt-1 font-medium">{{ errors.code }}</p>
          </div>

          <!-- Local Logo Image File Upload with Preview -->
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">
              Company Logo Image (Upload From Device)
            </label>
            <div class="border border-dashed border-[#db802d]/40 rounded-2xl p-4 bg-[#faf8f5] flex items-center gap-4">
              <!-- Logo Image Preview Container (Centered & Cover/Fit) -->
              <div class="w-16 h-16 rounded-2xl bg-transparent border border-neutral-200 flex items-center justify-center shrink-0 overflow-hidden shadow-xs">
                <img
                  v-if="logoPreviewUrl"
                  :src="logoPreviewUrl"
                  alt="Preview"
                  class="w-full h-full object-contain object-center"
                />
                <svg v-else class="w-7 h-7 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>

              <div class="flex-1 min-w-0">
                <input
                  ref="logoFileInput"
                  type="file"
                  accept="image/*"
                  @change="handleLogoFileSelect"
                  class="w-full text-xs text-neutral-600 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#db802d] file:text-white hover:file:bg-[#c46f20] cursor-pointer"
                />
                <span class="text-[11px] text-neutral-500 mt-1 block">Supports PNG, JPG, WEBP, SVG up to 5MB</span>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Status</label>
            <select
              v-model="form.status"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="pt-4 flex items-center justify-end gap-3 border-t border-neutral-100">
            <button
              type="button"
              @click="showModal = false"
              class="px-5 py-2.5 rounded-xl text-sm font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] disabled:opacity-50 cursor-pointer transition-all"
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
import { useNotificationStore } from '../../stores/notification'
import { useOrganizationStore } from '../../stores/organization'

const authStore = useAuthStore()
const orgStore = useOrganizationStore()
const notificationStore = useNotificationStore()

const companies = ref([])
const loading = ref(false)
const saving = ref(false)
const logoFileInput = ref(null)
const selectedLogoFile = ref(null)
const logoPreviewUrl = ref('')

const showModal = ref(false)
const isEditing = ref(false)
const currentId = ref(null)

const form = reactive({
  name: '',
  code: '',
  status: 'active',
})

const errors = reactive({
  name: '',
  code: '',
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

const handleLogoFileSelect = (e) => {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    notificationStore.error('Company logo must be under 5MB.')
    return
  }
  selectedLogoFile.value = file
  logoPreviewUrl.value = URL.createObjectURL(file)
}

const openCreateModal = () => {
  isEditing.value = false
  currentId.value = null
  form.name = ''
  form.code = ''
  form.status = 'active'
  selectedLogoFile.value = null
  logoPreviewUrl.value = ''
  errors.name = ''
  errors.code = ''
  if (logoFileInput.value) logoFileInput.value.value = ''
  showModal.value = true
}

// const getLogoUrl = (company) => {
//   if (!company) return ''
//   const url = typeof company === 'string' ? company : company.logo_url
//   if (!url) return ''
//   if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('blob:')) {
//     return url
//   }
//   const baseUrl = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api\/?$/, '')
//   // If we have company id, fallback stream endpoint is available
//   if (typeof company === 'object' && company.id) {
//     return `${baseUrl}/api/subsidiaries/${company.id}/logo`
//   }
//   return `${baseUrl}${url.startsWith('/') ? '' : '/'}${url}`
// }
const getLogoUrl = (company) => {
  const url = company?.logo_url
  if (!url) return ''

  if (/^(https?:|blob:|data:)/.test(url)) {
    return url
  }

  const baseUrl = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api')
    .replace(/\/api\/?$/, '')

  return `${baseUrl}${url.startsWith('/') ? '' : '/'}${url}`
}

const openEditModal = (company) => {
  isEditing.value = true
  currentId.value = company.id
  form.name = company.name
  form.code = company.code
  form.status = company.status
  selectedLogoFile.value = null
  logoPreviewUrl.value = getLogoUrl(company)
  errors.name = ''
  errors.code = ''
  if (logoFileInput.value) logoFileInput.value.value = ''
  showModal.value = true
}

const validateForm = () => {
  errors.name = ''
  errors.code = ''
  let valid = true

  if (!form.name.trim()) {
    errors.name = 'This field is required.'
    valid = false
  }

  if (!form.code.trim()) {
    errors.code = 'This field is required.'
    valid = false
  }

  return valid
}

const handleSubmit = async () => {
  if (!validateForm()) return

  saving.value = true
  try {
    const formData = new FormData()
    formData.append('name', form.name)
    formData.append('code', form.code)
    formData.append('status', form.status)

    if (selectedLogoFile.value) {
      formData.append('logo', selectedLogoFile.value)
    }

    if (isEditing.value) {
      formData.append('_method', 'PUT')
      await orgStore.updateCompany(currentId.value, formData)
      notificationStore.success('Company updated successfully.')
    } else {
      await orgStore.createCompany(formData)
      notificationStore.success('Company created successfully.')
    }
    showModal.value = false
    await loadCompanies()
  } catch (err) {
    if (err.response?.data?.errors) {
      const respErrors = err.response.data.errors
      if (respErrors.name) errors.name = respErrors.name[0]
      if (respErrors.code) errors.code = respErrors.code[0]
      notificationStore.error(err.response.data.message || 'Validation error saving company.')
    } else {
      notificationStore.error(err.response?.data?.message || 'Error saving company.')
    }
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
    notificationStore.info(`Company status changed to ${newStatus}.`)
    await loadCompanies()
  } catch (err) {
    notificationStore.error('Failed to update status.')
  }
}

onMounted(() => {
  loadCompanies()
})
</script>
