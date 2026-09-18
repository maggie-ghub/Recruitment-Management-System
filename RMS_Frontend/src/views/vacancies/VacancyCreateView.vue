<template>
  <div class="max-w-4xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Create Vacancy Draft</h1>
        <p class="text-xs text-[#7a6e5a] mt-0.5">
          Prepare a new vacancy requisition per FR-VAC-001 & FR-VAC-003
        </p>
      </div>
      <router-link to="/vacancies" class="text-xs text-neutral-600 hover:text-neutral-900 font-semibold">
        &larr; Back to Vacancies
      </router-link>
    </div>

    <!-- Form Container -->
    <form @submit.prevent="handleCreateVacancy" class="bg-white rounded-3xl border border-[#f0e9dc] p-6 sm:p-8 shadow-xs space-y-6">
      <!-- Section 1: Organizational Context (BR-003) -->
      <div>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#db802d] mb-4 pb-2 border-b border-neutral-100 flex items-center gap-2">
          1. Company & Department Assignment (BR-003)
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Company (Subsidiary) *</label>
            <select
              v-model="form.subsidiary_id"
              @change="onCompanyChange"
              required
              :disabled="!!authStore.user?.subsidiary_id"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630] disabled:bg-neutral-100"
            >
              <option value="" disabled>Select Company</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Department</label>
            <select
              v-model="form.department_id"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
            >
              <option value="">Unassigned / General</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Section 2: Position Details (FR-VAC-003) -->
      <div>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#db802d] mb-4 pb-2 border-b border-neutral-100 flex items-center gap-2">
          2. Position Core Attributes
        </h2>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Position Title *</label>
            <input
              v-model="form.title"
              type="text"
              required
              placeholder="e.g. Senior Process Automation Specialist"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Number of Openings *</label>
              <input
                v-model.number="form.openings"
                type="number"
                min="1"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Employment Type *</label>
              <select
                v-model="form.employment_type"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
              >
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Remote">Remote</option>
                <option value="Hybrid">Hybrid</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Location *</label>
              <input
                v-model="form.location"
                type="text"
                required
                placeholder="e.g. Addis Ababa / Dukem"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Grade / Level</label>
              <input
                v-model="form.grade_level"
                type="text"
                placeholder="e.g. Level IV / Senior Professional"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Reports To Position</label>
              <input
                v-model="form.reports_to"
                type="text"
                placeholder="e.g. Head of Engineering"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-1">
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Closing Date *</label>
              <input
                v-model="form.closing_date"
                type="date"
                required
                :min="today"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
              />
            </div>

            <!-- Confidential Internal Salary (BR-005) -->
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">
                Internal Salary Amount 🔒
              </label>
              <input
                v-model.number="form.salary_amount"
                type="number"
                min="0"
                placeholder="Confidential"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
              />
              <span class="text-[10px] text-amber-700 font-medium">Never displayed to applicants (BR-005)</span>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Currency</label>
              <select
                v-model="form.salary_currency"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
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
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#db802d] mb-4 pb-2 border-b border-neutral-100 flex items-center gap-2">
          3. Job Description & Criteria
        </h2>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Job Description *</label>
            <textarea
              v-model="form.description"
              rows="3"
              required
              placeholder="High level overview of the position..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Key Responsibilities *</label>
            <textarea
              v-model="form.responsibilities"
              rows="4"
              required
              placeholder="Detailed duties and day-to-day responsibilities..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Qualifications & Requirements *</label>
            <textarea
              v-model="form.qualifications"
              rows="4"
              required
              placeholder="Degrees, certifications, and required background..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Years of Experience</label>
              <input
                v-model="form.experience_years"
                type="text"
                placeholder="e.g. 3-5 years"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Application Special Requirements</label>
              <input
                v-model="form.application_requirements"
                type="text"
                placeholder="e.g. Certified transcript required"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Submit button -->
      <div class="pt-4 border-t border-neutral-100 flex items-center justify-end gap-3">
        <router-link
          to="/vacancies"
          class="px-5 py-2.5 rounded-xl text-sm font-semibold text-neutral-600 hover:bg-neutral-100"
        >
          Cancel
        </router-link>
        <button
          type="submit"
          :disabled="saving"
          class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] disabled:opacity-50 cursor-pointer shadow-md"
        >
          {{ saving ? 'Saving Draft...' : 'Save Vacancy Draft' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useOrganizationStore } from '../../stores/organization'
import { useVacancyStore } from '../../stores/vacancy'

const router = useRouter()
const authStore = useAuthStore()
const orgStore = useOrganizationStore()
const vacancyStore = useVacancyStore()

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

const onCompanyChange = async () => {
  if (form.subsidiary_id) {
    departments.value = await orgStore.fetchDepartments(form.subsidiary_id)
  } else {
    departments.value = []
  }
}

const handleCreateVacancy = async () => {
  saving.value = true
  try {
    const res = await vacancyStore.createVacancy(form)
    alert('Vacancy draft created successfully.')
    router.push(`/vacancies/${res.data.id}`)
  } catch (err) {
    alert(err.response?.data?.message || 'Error creating vacancy.')
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
