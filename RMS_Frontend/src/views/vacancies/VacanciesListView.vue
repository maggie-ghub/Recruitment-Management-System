<template>
  <div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">
          {{ authStore.isApplicant ? 'Career Opportunities' : 'Vacancy Management' }}
        </h1>
        <p class="text-xs text-[#7a6e5a] mt-0.5">
          {{ authStore.isApplicant 
              ? 'Explore open positions across Tora Holding Company subsidiaries' 
              : 'Prepare drafts, submit for approval, publish, and track vacancy lifecycle (FR-VAC-001 to FR-VAC-008)' 
          }}
        </p>
      </div>

      <router-link
        v-if="canCreateVacancy"
        to="/vacancies/create"
        class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] shadow-sm transition-all cursor-pointer"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Vacancy Draft
      </router-link>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-[#f0e9dc] shadow-xs flex flex-wrap items-center gap-3">
      <!-- Search input -->
      <div class="flex-1 min-w-[220px]">
        <input
          v-model="vacancyStore.filters.search"
          @input="loadVacancies"
          type="text"
          placeholder="Search by title, reference code, or location..."
          class="w-full px-3.5 py-2 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#ebb630] bg-neutral-50"
        />
      </div>

      <!-- Company filter (if Holding user) -->
      <select
        v-if="!authStore.user?.subsidiary_id"
        v-model="vacancyStore.filters.subsidiary_id"
        @change="loadVacancies"
        class="px-3 py-2 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#ebb630] bg-neutral-50"
      >
        <option value="">All Companies</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>

      <!-- Status filter (internal recruitment staff only) -->
      <select
        v-if="!authStore.isApplicant"
        v-model="vacancyStore.filters.status"
        @change="loadVacancies"
        class="px-3 py-2 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#ebb630] bg-neutral-50"
      >
        <option value="">All Statuses</option>
        <option value="Draft">Draft</option>
        <option value="Pending Approval">Pending Approval</option>
        <option value="Approved">Approved</option>
        <option value="Published">Published</option>
        <option value="Closed">Closed</option>
        <option value="Archived">Archived</option>
      </select>

      <!-- Employment Type filter -->
      <select
        v-model="vacancyStore.filters.employment_type"
        @change="loadVacancies"
        class="px-3 py-2 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#ebb630] bg-neutral-50"
      >
        <option value="">All Types</option>
        <option value="Full-time">Full-time</option>
        <option value="Part-time">Part-time</option>
        <option value="Contract">Contract</option>
        <option value="Remote">Remote</option>
        <option value="Hybrid">Hybrid</option>
      </select>
    </div>

    <!-- Vacancies List -->
    <div v-if="vacancyStore.loading" class="bg-white rounded-3xl border border-[#f0e9dc] p-12 text-center text-sm text-[#7a6e5a]">
      Loading vacancies...
    </div>

    <div v-else-if="vacancyStore.vacancies.length === 0" class="bg-white rounded-3xl border border-[#f0e9dc] p-12 text-center text-neutral-500">
      No vacancies found matching the current criteria.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="vacancy in vacancyStore.vacancies"
        :key="vacancy.id"
        class="bg-white rounded-3xl border border-[#f0e9dc] p-5 shadow-xs hover:border-[#ebb630] hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <!-- Top Row: Company & Status Badges -->
          <div class="flex items-center justify-between gap-2 mb-2.5">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-[#db802d] border border-amber-200">
              {{ vacancy.subsidiary?.name }}
            </span>
            <span
              class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
              :class="getStatusBadgeClass(vacancy.status)"
            >
              {{ vacancy.status }}
            </span>
          </div>

          <!-- Title & Reference -->
          <h3 class="font-bold text-base text-neutral-900 leading-snug">
            {{ vacancy.title }}
          </h3>
          <div class="text-[11px] text-[#7a6e5a] font-mono mt-0.5">
            Ref: {{ vacancy.reference_number }}
          </div>

          <!-- Meta Pills -->
          <div class="flex flex-wrap items-center gap-2 mt-3 text-xs text-neutral-600">
            <span class="inline-flex items-center gap-1 bg-[#faf8f5] px-2.5 py-1 rounded-lg border border-[#f0e9dc]">
              <svg class="w-3.5 h-3.5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              {{ vacancy.location }}
            </span>

            <span class="inline-flex items-center gap-1 bg-[#faf8f5] px-2.5 py-1 rounded-lg border border-[#f0e9dc]">
              <svg class="w-3.5 h-3.5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              {{ vacancy.employment_type }}
            </span>

            <span class="inline-flex items-center gap-1 bg-[#faf8f5] px-2.5 py-1 rounded-lg border border-[#f0e9dc]">
              Openings: {{ vacancy.openings }}
            </span>

            <!-- Internal Salary Tag (BR-005: only visible to internal staff, never applicants) -->
            <span
              v-if="vacancy.salary_amount && !authStore.isApplicant"
              class="inline-flex items-center gap-1 bg-amber-100/70 text-amber-900 px-2 py-0.5 rounded-lg text-[11px] font-bold border border-amber-200"
              title="Confidential Internal Salary"
            >
              🔒 {{ vacancy.salary_amount }} {{ vacancy.salary_currency }}
            </span>
          </div>

          <!-- Description snippet -->
          <p class="text-xs text-neutral-600 line-clamp-2 mt-3 leading-relaxed">
            {{ vacancy.description }}
          </p>
        </div>

        <!-- Footer / Action Row -->
        <div class="mt-4 pt-3 border-t border-neutral-100 flex items-center justify-between">
          <div class="text-[11px] text-[#7a6e5a]">
            Closing: <strong class="text-neutral-700">{{ new Date(vacancy.closing_date).toLocaleDateString() }}</strong>
          </div>

          <router-link
            :to="`/vacancies/${vacancy.id}`"
            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] transition-colors"
          >
            {{ authStore.isApplicant ? 'View & Apply' : 'Manage Vacancy' }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useOrganizationStore } from '../../stores/organization'
import { useVacancyStore } from '../../stores/vacancy'

const authStore = useAuthStore()
const orgStore = useOrganizationStore()
const vacancyStore = useVacancyStore()

const companies = ref([])

const canCreateVacancy = computed(() => {
  return ['Admin', 'HR Manager', 'Recruiter'].includes(authStore.role)
})

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Draft': return 'bg-neutral-100 text-neutral-700'
    case 'Pending Approval': return 'bg-amber-100 text-amber-800'
    case 'Approved': return 'bg-blue-100 text-blue-800'
    case 'Published': return 'bg-emerald-100 text-emerald-800'
    case 'Closed': return 'bg-rose-100 text-rose-800'
    case 'Archived': return 'bg-neutral-200 text-neutral-800'
    default: return 'bg-neutral-100 text-neutral-600'
  }
}

const loadVacancies = async () => {
  await vacancyStore.fetchVacancies()
}

onMounted(async () => {
  companies.value = await orgStore.fetchCompanies('active')
  await loadVacancies()
})
</script>
