<template>
  <div class="max-w-5xl mx-auto space-y-6 pb-12">
    <!-- Header with modern SaaS Breadcrumb / Back button -->
    <div class="flex items-center justify-between">
      <div>
        <div class="flex items-center gap-2 text-xs text-[#7a6e5a] mb-1">
          <router-link to="/vacancies" class="hover:text-[#db802d] transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Vacancies
          </router-link>
          <span>/</span>
          <span class="text-neutral-800 font-medium">New Position</span>
        </div>
        <h1 class="text-3xl font-extrabold text-neutral-900 tracking-tight">Create Vacancy</h1>
        <p class="text-sm text-[#7a6e5a] mt-1">
          Configure a new recruitment requisition for your company
        </p>
      </div>

      <router-link
        to="/vacancies"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-neutral-200 bg-white text-xs font-semibold text-neutral-700 hover:bg-neutral-50 hover:border-neutral-300 transition-all shadow-2xs"
      >
        <svg class="w-4 h-4 text-[#7a6e5a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Cancel
      </router-link>
    </div>

    <!-- Form Container with Larger Screen Size, Standardized Placeholders & PascalCase -->
    <form @submit.prevent="handleCreateVacancy" novalidate class="bg-white rounded-3xl border border-[#f0e9dc] p-7 sm:p-9 shadow-xs space-y-7">
      <!-- Section 1: Organizational Context -->
      <div>
        <h2 class="text-xs font-bold tracking-wider text-[#db802d] mb-4 pb-2 border-b border-neutral-100 flex items-center gap-2">
          1. Company & Department Assignment
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Company *</label>
            <select
              v-model="form.subsidiary_id"
              @change="onCompanyChange"
              :disabled="!!authStore.user?.subsidiary_id"
              class="w-full pl-4 pr-8 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] disabled:bg-neutral-100 cursor-pointer"
              :class="errors.subsidiary_id ? 'border-red-400 bg-red-50/20' : ''"
            >
              <option value="" disabled>Select Company</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <p v-if="errors.subsidiary_id" class="text-xs text-red-600 mt-1 font-medium">{{ errors.subsidiary_id }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Department</label>
            <select
              v-model="form.department_id"
              class="w-full pl-4 pr-8 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] cursor-pointer"
            >
              <option value="">Unassigned / General</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Section 2: Position Details -->
      <div>
        <h2 class="text-xs font-bold tracking-wider text-[#db802d] mb-4 pb-2 border-b border-neutral-100 flex items-center gap-2">
          2. Position Attributes
        </h2>

        <div class="space-y-5">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Position Title *</label>
            <input
              v-model="form.title"
              type="text"
              placeholder="Enter position title"
              class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="errors.title ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="errors.title" class="text-xs text-red-600 mt-1 font-medium">{{ errors.title }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Number Of Openings *</label>
              <input
                v-model.number="form.openings"
                type="number"
                min="1"
                placeholder="Enter number of openings"
                class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
                :class="errors.openings ? 'border-red-400 bg-red-50/20' : ''"
              />
              <p v-if="errors.openings" class="text-xs text-red-600 mt-1 font-medium">{{ errors.openings }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Employment Type *</label>
              <select
                v-model="form.employment_type"
                class="w-full pl-4 pr-8 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] cursor-pointer"
              >
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Remote">Remote</option>
                <option value="Hybrid">Hybrid</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Location *</label>
              <input
                v-model="form.location"
                type="text"
                placeholder="Enter job location"
                class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
                :class="errors.location ? 'border-red-400 bg-red-50/20' : ''"
              />
              <p v-if="errors.location" class="text-xs text-red-600 mt-1 font-medium">{{ errors.location }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Grade / Level</label>
              <input
                v-model="form.grade_level"
                type="text"
                placeholder="Enter grade level"
                class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Reports To Position</label>
              <input
                v-model="form.reports_to"
                type="text"
                placeholder="Enter supervisor title"
                class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="sm:col-span-1">
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Closing Date *</label>
              <input
                v-model="form.closing_date"
                type="date"
                :min="today"
                class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
                :class="errors.closing_date ? 'border-red-400 bg-red-50/20' : ''"
              />
              <p v-if="errors.closing_date" class="text-xs text-red-600 mt-1 font-medium">{{ errors.closing_date }}</p>
            </div>

            <!-- Internal Salary without icon -->
            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">
                Internal Salary Amount
              </label>
              <input
                v-model.number="form.salary_amount"
                type="number"
                min="0"
                max="999999999"
                step="0.01"
                placeholder="Enter salary amount"
                class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
                :class="errors.salary_amount ? 'border-red-400 bg-red-50/20' : ''"
              />
              <p v-if="errors.salary_amount" class="text-xs text-red-600 mt-1 font-medium">{{ errors.salary_amount }}</p>
              <span v-else class="text-[11px] text-amber-700 font-medium mt-1 block">Confidential (never displayed to applicants)</span>
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Currency</label>
              <select
                v-model="form.salary_currency"
                class="w-full pl-4 pr-8 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] cursor-pointer"
              >
                <option value="ETB">ETB (Ethiopian Birr)</option>
                <option value="USD">USD (US Dollar)</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 3: Requisition Content -->
      <div>
        <h2 class="text-xs font-bold tracking-wider text-[#db802d] mb-4 pb-2 border-b border-neutral-100 flex items-center gap-2">
          3. Job Description & Criteria
        </h2>

        <div class="space-y-5">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Job Description *</label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Enter position description"
              class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="errors.description ? 'border-red-400 bg-red-50/20' : ''"
            ></textarea>
            <p v-if="errors.description" class="text-xs text-red-600 mt-1 font-medium">{{ errors.description }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Key Responsibilities *</label>
            <textarea
              v-model="form.responsibilities"
              rows="4"
              placeholder="Enter key responsibilities"
              class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="errors.responsibilities ? 'border-red-400 bg-red-50/20' : ''"
            ></textarea>
            <p v-if="errors.responsibilities" class="text-xs text-red-600 mt-1 font-medium">{{ errors.responsibilities }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Qualifications & Requirements *</label>
            <textarea
              v-model="form.qualifications"
              rows="4"
              placeholder="Enter required qualifications and certifications"
              class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="errors.qualifications ? 'border-red-400 bg-red-50/20' : ''"
            ></textarea>
            <p v-if="errors.qualifications" class="text-xs text-red-600 mt-1 font-medium">{{ errors.qualifications }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Minimum Experience (Years)</label>
              <input
                v-model="form.experience_years"
                type="text"
                placeholder="Enter minimum experience"
                class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1.5">Application Specific Instructions</label>
              <input
                v-model="form.application_requirements"
                type="text"
                placeholder="Enter application instructions"
                class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="pt-6 border-t border-neutral-100 flex items-center justify-end gap-3.5">
        <router-link
          to="/vacancies"
          class="px-6 py-3 rounded-2xl text-sm font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer transition-all"
        >
          Cancel
        </router-link>
        <button
          type="submit"
          :disabled="saving"
          class="px-7 py-3 rounded-2xl text-sm font-bold text-white bg-[#db802d] hover:bg-[#c46f20] shadow-sm disabled:opacity-50 cursor-pointer transition-all"
        >
          {{ saving ? 'Saving...' : 'Create Vacancy' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useNotificationStore } from '../../stores/notification'
import { useOrganizationStore } from '../../stores/organization'
import { useVacancyStore } from '../../stores/vacancy'

const router = useRouter()
const authStore = useAuthStore()
const orgStore = useOrganizationStore()
const vacancyStore = useVacancyStore()
const notificationStore = useNotificationStore()

const companies = ref([])
const departments = ref([])
const saving = ref(false)

const today = new Date().toISOString().split('T')[0]

const form = reactive({
  subsidiary_id: authStore.user?.subsidiary_id || '',
  department_id: '',
  title: '',
  openings: 1,
  employment_type: 'Full-time',
  location: '',
  grade_level: '',
  reports_to: '',
  salary_amount: null,
  salary_currency: 'ETB',
  closing_date: '',
  description: '',
  responsibilities: '',
  qualifications: '',
  experience_years: '',
  application_requirements: '',
})

const errors = reactive({
  subsidiary_id: '',
  title: '',
  openings: '',
  location: '',
  closing_date: '',
  description: '',
  responsibilities: '',
  qualifications: '',
  salary_amount: '',
})

const onCompanyChange = async () => {
  if (form.subsidiary_id) {
    departments.value = await orgStore.fetchDepartments(form.subsidiary_id)
  } else {
    departments.value = []
  }
}

const validate = () => {
  let valid = true
  Object.keys(errors).forEach((k) => (errors[k] = ''))

  if (!form.subsidiary_id) {
    errors.subsidiary_id = 'This field is required.'
    valid = false
  }

  if (!form.title.trim()) {
    errors.title = 'This field is required.'
    valid = false
  }

  if (!form.openings || form.openings < 1) {
    errors.openings = 'Please enter a valid number of openings (minimum 1).'
    valid = false
  }

  if (!form.location.trim()) {
    errors.location = 'This field is required.'
    valid = false
  }

  if (!form.closing_date) {
    errors.closing_date = 'This field is required.'
    valid = false
  }

  if (!form.description.trim()) {
    errors.description = 'This field is required.'
    valid = false
  }

  if (!form.responsibilities.trim()) {
    errors.responsibilities = 'This field is required.'
    valid = false
  }

  if (!form.qualifications.trim()) {
    errors.qualifications = 'This field is required.'
    valid = false
  }

  return valid
}

const handleCreateVacancy = async () => {
  if (!validate()) {
    notificationStore.error('Please complete all required fields.')
    return
  }

  saving.value = true
  try {
    const res = await vacancyStore.createVacancy(form)
    notificationStore.success('Vacancy created successfully.')
    router.push(`/vacancies/${res.data.id}`)
  } catch (err) {
    if (err.response?.data?.errors) {
      const respErrors = err.response.data.errors
      Object.keys(respErrors).forEach((field) => {
        if (errors[field] !== undefined) {
          errors[field] = respErrors[field][0]
        }
      })
      notificationStore.error('Please review the highlighted validation errors.')
    } else {
      notificationStore.error(err.response?.data?.message || 'Error creating vacancy.')
    }
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  companies.value = await orgStore.fetchCompanies('active')
  if (form.subsidiary_id) {
    await onCompanyChange()
  }
})
</script>
